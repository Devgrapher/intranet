<?php

namespace Intra\Controller;

use Intra\Core\MsgException;
use Intra\Service\File\SupportFileService;
use Intra\Service\Support\Column\SupportColumnCategory;
use Intra\Service\Support\Column\SupportColumnTeam;
use Intra\Service\Support\Column\SupportColumnWorker;
use Intra\Service\Support\SupportDinnerService;
use Intra\Service\Support\SupportDto;
use Intra\Service\Support\SupportPolicy;
use Intra\Service\Support\SupportRowService;
use Intra\Service\Support\SupportViewDtoFactory;
use Intra\Service\User\Organization;
use Intra\Service\User\UserDtoFactory;
use Intra\Service\User\UserPolicy;
use Intra\Service\User\UserSession;
use Intra\Service\Util\Util;
use Ridibooks\Platform\Common\CsvResponse;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SupportController implements ControllerProviderInterface
{
    const MSG_URL_NOT_EXISTS = '연결된 링크가 없습니다. 관리자에게 문의해주세요.';

    public function connect(Application $app)
    {
        /**
         * @var ControllerCollection
         */
        $controller_collection = $app['controllers_factory'];

        $controller_collection->get('/dinner', [$this, 'orderDinner']);
        $controller_collection->get('/delivery', [$this, 'orderDelivery']);
        $controller_collection->get('/present', [$this, 'orderGuestPresent']);

        $controller_collection->get('/{target}', [$this, 'index']);
        $controller_collection->get('/{target}/{type}', [$this, 'index']);
        $controller_collection->get('/{target}/uid/{uid}/yearmonth/{yearmonth}', [$this, 'index']);

        $controller_collection->post('/{target}/add', [$this, 'add']);
        $controller_collection->match('/{target}/id/{id}', [$this, 'edit'])->method('PUT|POST');
        $controller_collection->match('/{target}/id/{id}/complete', [$this, 'edit'])->method('PUT|POST')->value('type', 'complete');

        $controller_collection->delete('/{target}/id/{id}', [$this, 'del']);
        $controller_collection->get('/{target}/const/{key}', [$this, 'constVaules']);

        $controller_collection->post('/{target}/file_upload', [$this, 'fileUpload']);
        $controller_collection->get('/{target}/{column}/file/{fileid}', [$this, 'fileDownload']);
        $controller_collection->delete('/{target}/file/{fileid}', [$this, 'fileDelete']);

        $controller_collection->get('/{target}/download/{type}/{yearmonth}', [$this, 'excelDownload']);

        return $controller_collection;
    }

    public function index(Request $request, Application $app)
    {
        $self = UserSession::getSelfDto();
        $target = $request->get('target');
        $yearmonth = $request->get('yearmonth');
        $uid = $request->get('uid');
        $type = $request->get('type');

        if (!strlen($yearmonth)) {
            $yearmonth = date('Y-m');
        }
        $date = $yearmonth . '-01';
        if (!intval($uid) || !UserPolicy::isSupportAdmin($self, $target)) {
            $uid = $self->uid;
        }

        $prev_yearmonth = date('Y-m', strtotime('-1 month', strtotime($yearmonth)));
        $next_yearmonth = date('Y-m', strtotime('+1 month', strtotime($yearmonth)));

        $columns = SupportPolicy::getColumnFieldsTestUserDto($target, $self);
        $title = SupportPolicy::getColumnTitle($target);
        $const = [
            'teams' => Organization::readTeamNames(),
            'managers' => UserDtoFactory::createManagerUserDtos(),
            'users' => UserDtoFactory::createAvailableUserDtos(),
        ];
        $support_view_dtos = SupportViewDtoFactory::gets($columns, $target, $uid, $date, $type);

        $explain = SupportPolicy::getExplain($target);

        return $app['twig']->render('support/index.twig', [
            'uid' => $uid,
            'prev_yearmonth' => $prev_yearmonth,
            'yearmonth' => $yearmonth,
            'next_yearmonth' => $next_yearmonth,
            'target' => $target,
            'title' => $title,
            'columns' => $columns,
            'support_view_dtos' => $support_view_dtos,
            'const' => $const,
            'is_admin' => UserPolicy::isSupportAdmin($self, $target),
            'all_users' => UserDtoFactory::createAllUserDtos(),
            'explain' => $explain,
        ]);
    }

    public function add(Request $request, Application $app)
    {
        $self = UserSession::getSelfDto();

        $target = $request->get('target');

        $columns = SupportPolicy::getColumnFields($target);
        $uid = $request->get('uid');
        if (!intval($uid) || !UserPolicy::isSupportAdmin($self, $target)) {
            $uid = $self->uid;
        }

        $support_dto = SupportDto::importFromAddRequest($request, $uid, $columns);
        $target_user_dto = UserDtoFactory::createByUid($uid);

        return SupportRowService::add($target_user_dto, $support_dto, $app);
    }

    public function constVaules(Request $request)
    {
        $target = $request->get('target');
        $key = $request->get('key');

        $columns = SupportPolicy::getColumnFields($target);
        $return = [];
        foreach ($columns as $column) {
            if ($key == $column->key) {
                if ($column instanceof SupportColumnTeam) {
                    foreach (Organization::readTeamNames() as $team) {
                        $return[$team] = $team;
                    }
                } elseif ($column instanceof SupportColumnWorker) {
                    foreach (UserDtoFactory::createAvailableUserDtos() as $user_dto) {
                        $return[$user_dto->uid] = $user_dto->name;
                    }
                } elseif ($column instanceof SupportColumnCategory) {
                    foreach ($column->category_items as $category_item) {
                        $return[$category_item] = $category_item;
                    }
                }
            }
        }
        return new JsonResponse($return);
    }

    public function del(Request $request, Application $app)
    {
        $target = $request->get('target');
        $id = $request->get('id');

        return SupportRowService::del($target, $id, $app);
    }

    public function edit(Request $request, Application $app)
    {
        $target = $request->get('target');
        $id = $request->get('id');
        $key = $request->get('key');
        $value = $request->get('value');

        $type = $request->get('type');
        if ($type == 'complete') {
            return SupportRowService::complete($target, $id, $key, $app);
        }
        return SupportRowService::edit($target, $id, $key, $value, $app);
    }

    public function excelDownload(Request $request)
    {
        $self = UserSession::getSelfDto();

        $target = $request->get('target');
        $type = $request->get('type');
        $yearmonth = $request->get('yearmonth');
        if ($type == 'year') {
            $date = date_create($yearmonth . '-01');
            $begin_datetime = (clone $date)->modify("first day of this year");
            $end_datetime = (clone $begin_datetime)->modify("first day of next year");
        } elseif ($type == 'yearmonth') {
            $begin_datetime = date_create($yearmonth . '-01');
            $end_datetime = (clone $begin_datetime)->modify("first day of this month next month");
        } else {
            throw new MsgException('invalid type');
        }

        $columns = SupportPolicy::getColumnFieldsTestUserDto($target, $self);
        $support_view_dtos = SupportViewDtoFactory::getsForExcel($columns, $target, $begin_datetime, $end_datetime);

        $csvs = [];
        $csv_header = [];
        foreach ($columns as $column_name => $column) {
            $csv_header[] = $column_name;
        }
        $csv_header = $this->excelPostworkHeader($csv_header, $target);
        $csvs[] = $csv_header;

        foreach ($support_view_dtos as $support_view_dto) {
            $csv_row = [];
            foreach ($columns as $column_name => $column) {
                $csv_row[$column_name] = $support_view_dto->display_dict[$column->key];
            }
            $csv_row = $this->excelPostworkBody($csv_row, $target);
            $csvs[] = $csv_row;
        }

        return CsvResponse::create($csvs);
    }

    public function fileDelete(Request $request)
    {
        $self = UserSession::getSelfDto();
        $target = $request->get('target');
        $column_key = $request->get('column_key');
        $file_id = $request->get('fileid');
        if (!intval($file_id)) {
            throw new MsgException("invalid file_id");
        }

        if (SupportFileService::deleteFile($self, $target, $column_key, $file_id)) {
            return Response::create('success');
        } else {
            return Response::create('삭제실패했습니다.');
        }
    }

    public function fileUpload(Request $request)
    {
        $target = $request->get('target');
        $column_key = $request->get('column_key');
        $id = $request->get('id');
        if (!intval($id)) {
            throw new MsgException("invalid paymentid");
        }

        /* @var UploadedFile $file */
        $file = $request->files->get('files')[0];

        $self = UserSession::getSelfDto();
        $file_service = new SupportFileService($target, $column_key);
        $file = $file_service->uploadFile(
            $self->uid,
            $id,
            $file->getClientOriginalName(),
            file_get_contents($file->getRealPath())
        );

        if (!$file) {
            return JsonResponse::create('file upload failed', 500);
        }

        return JsonResponse::create('success');
    }

    public function fileDownload(Request $request)
    {
        $target = $request->get('target');
        $column_key = $request->get('column');
        $id = $request->get('fileid');

        $file_service = new SupportFileService($target, $column_key);
        $file = $file_service->getFileWithId($id);
        return RedirectResponse::create($file['location']);
    }

    public function fileDownload(Request $request)
    {
        $self = UserSession::getSelfDto();
        $target = $request->get('target');
        $column_key = $request->get('column');
        $file_id = $request->get('fileid');

        return SupportFileService::downloadFile($self, $target, $column_key, $file_id);
    }

    private function excelPostworkHeader($csv_header, $target)
    {
        if ($target == SupportPolicy::TYPE_BUSINESS_CARD) {
            $csv_header = array_merge($csv_header, [
                '이름 - 출력용 시작',
                '영문명',
                '부서명',
                '직급(한글)',
                '직급(영문)',
                'MOBILE',
                'E-MAIL',
                'PHONE(내선)',
                'FAX',
                '주소',
                '수량',
                '제작예정일'
            ]);
        }
        return $csv_header;
    }

    private function excelPostworkBody($csv_row, $target)
    {
        if ($target == SupportPolicy::TYPE_BUSINESS_CARD) {
            if ($csv_row['대상자'] == '직원') {
                $csv_row[] = $csv_row['대상자(직원)'];
            } else {
                $csv_row[] = $csv_row['대상자(현재 미입사)'];
            }
            $csv_row[] = $csv_row['영문명'];
            if ($csv_row['부서명'] == '기타') {
                $csv_row[] = $csv_row['부서명(기타)'];
            } else {
                $csv_row[] = $csv_row['부서명'];
            }
            $csv_row[] = $csv_row['직급(한글)'];
            $csv_row[] = $csv_row['직급(영문)'];
            $csv_row[] = $csv_row['MOBILE'];
            $csv_row[] = $csv_row['E-MAIL'];
            $csv_row[] = $csv_row['PHONE(내선)'];
            $csv_row[] = $csv_row['FAX'];
            $csv_row[] = $csv_row['주소'];
            if ($csv_row['수량'] == '기타 - 50매 단위') {
                $csv_row[] = $csv_row['수량(기타)'];
            } else {
                $csv_row[] = $csv_row['수량'];
            }
            $csv_row[] = $csv_row['제작(예정)일'];
        }
        return $csv_row;
    }

    public function orderDinner()
    {
        return SupportDinnerService::getResponse();
    }

    public function orderDelivery()
    {
        if (empty($_ENV['delivery_order_url'])) {
            return new Response(Util::printAlert(self::MSG_URL_NOT_EXISTS));
        }
        return new RedirectResponse($_ENV['delivery_order_url']);
    }

    public function orderGuestPresent()
    {
        if (empty($_ENV['guest_present_order_url'])) {
            return new Response(Util::printAlert(self::MSG_URL_NOT_EXISTS));
        }
        return new RedirectResponse($_ENV['guest_present_order_url']);
    }
}
