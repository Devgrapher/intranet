<?php
namespace Intra\Service\Payment;

use Intra\Core\BaseDto;
use Intra\Core\MsgException;
use Intra\Service\User\Organization;
use Intra\Service\User\UserJoinService;
use Ridibooks\Platform\Common\DateUtil;
use Symfony\Component\HttpFoundation\Request;

class PaymentDto extends BaseDto
{
    public $paymentid;
    public $uuid;
    public $uid;
    public $manager_uid;
    public $request_date;
    public $month;
    public $team;
    public $team_detail;
    public $product;
    public $category;
    public $desc;
    public $company_name;
    public $bank;
    public $bank_account;
    public $bank_account_owner;
    public $price;
    public $pay_date;
    public $tax;
    public $note;
    public $paytype;
    public $status;
    public $tax_export;
    public $tax_date;
    public $is_account_book_registered;

    /**
     * html view only
     */
    public $is_editable;
    public $register_name;
    public $manager_name;

    /**
     * @var PaymentAcceptDto
     */
    public $manger_accept;
    public $is_manager_accepted;

    /**
     * @var PaymentAcceptDto
     */
    public $co_accept;
    public $co_accpeter_name;
    public $is_co_accepted;

    /**
     * @var FileUploadDto[]
     */
    public $files;
    public $is_file_uploadable;

    /**
     * @param array $payment_row []
     * @param $payment_accepts_dtos PaymentAcceptDto[]
     * @param $payment_files_dtos FileUploadDto[]
     * @return PaymentDto
     */
    public static function importFromDatabase(array $payment_row, array $payment_accepts_dtos, $payment_files_dtos)
    {
        $return = new self();
        $return->initFromArray($payment_row);
        $return->register_name = UserJoinService::getNameByUidSafe($return->uid);
        $return->manager_name = UserJoinService::getNameByUidSafe($return->manager_uid);

        $return->is_manager_accepted = false;
        $return->is_co_accepted = false;

        $return->files = $payment_files_dtos;
        $return->is_file_uploadable = (!in_array($return->status, ['결제 완료', '삭제']));

        foreach ($payment_accepts_dtos as $payment_accept) {
            if ($payment_accept->paymentid == $return->paymentid) {
                if ($payment_accept->user_type == 'manager') {
                    $return->manger_accept = $payment_accept;
                    $return->is_manager_accepted = true;
                }
                if ($payment_accept->user_type == 'co') {
                    $return->co_accept = $payment_accept;
                    $return->is_co_accepted = true;
                    $return->co_accpeter_name = UserJoinService::getNameByUidSafe($payment_accept->uid);
                }
            }
        }
        if (!$return->is_manager_accepted && !$return->is_co_accepted) {
            $return->is_editable = true;
        } else {
            $return->is_editable = false;
        }

        return $return;
    }

    public static function importFromAddRequest(Request $request, $uid, $is_admin)
    {
        $return = new self();
        $keys = [
            'month',
            'manager_uid',
            'team',
            'team_detail',
            'product',
            'category',
            'desc',
            'company_name',
            'price',
            'bank',
            'bank_account',
            'bank_account_owner',
            'pay_date',
            'tax',
            'tax_export',
            'tax_date',
            'is_account_book_registered',
            'note',
            'paytype',
            'status',
        ];
        foreach ($keys as $key) {
            $return->$key = $request->get($key);
        }

        $return->uid = $uid;
        if (!$is_admin) {
            if (isset($return->status)) {
                $return->status = null;
            }
            if (isset($return->paytype)) {
                $return->paytype = null;
            }
        }

        $return->request_date = date('Y-m-d');
        $return->month = preg_replace('/\D/', '/', trim($return->month));
        $return->month = date('Y-m', strtotime($return->month . '/1'));
        $return->pay_date = preg_replace('/\D/', '-', trim($return->pay_date));
        $return->price = empty($return->price) ? 0 : $return->price;
        $return->tax_date = $return->tax_date === '' ? null : $return->tax_date;
        if (isset($return->status) && strlen($return->status) == 0) {
            $return->status = null;
        }
        if (empty($return->manager_uid)) {
            throw new MsgException('승인자가 누락되었습니다. 다시 입력해주세요');
        }
        if (empty($return->team)) {
            throw new MsgException('귀속부서가 누락되었습니다. 다시 입력해주세요');
        }
        if (empty($return->product)) {
            throw new MsgException('프로덕트가 누락되었습니다. 다시 입력해주세요');
        }
        if (empty($return->category)) {
            throw new MsgException('분류가 누락되었습니다. 다시 입력해주세요');
        }
        if (strlen($return->paytype) == 0) {
            $return->paytype = null;
        }
        if (!strtotime($return->month . '-1')) {
            throw new MsgException('귀속월을 다시 입력해주세요');
        }
        if (!strtotime($return->pay_date)) {
            throw new MsgException('결제(예정)일을 다시 입력해주세요');
        }
        if (strtotime($return->pay_date) < strtotime(date('Y-m-d'))) {
            throw new MsgException('결제(예정)일은 과거일자로 지정할 수 없습니다.');
        }
        if (!in_array($return->tax, UserPaymentConst::getByKey('tax'))) {
            throw new MsgException('세금계산서수취여부를 다시 입력해주세요');
        }
        if ($return->tax == 'Y' && !$return->tax_date) {
            throw new MsgException('세금계산서를 수취한 경우, 세금계산서일자를 입력해야 합니다.');
        }
        if (DateUtil::isWeekend($return->pay_date)) {
            throw new MsgException('결제(예정)일을 주말로 설정할 수 없습니다');
        }
        if ($return->team == Organization::getTeamName(Organization::ALIAS_ROMANCE_BL) &&
            (!$return->team_detail || $return->team_detail === '없음')) {
            throw new MsgException($return->team . ' 부서인 경우 부서 세부분류를 선택해야 합니다.');
        }
        if ($return->team != Organization::getTeamName(Organization::ALIAS_ROMANCE_BL)) {
            $return->team_detail = '';
        }
        if (!$return->is_account_book_registered) {
            $return->is_account_book_registered = 'N';
        }

        return $return;
    }

    public function exportDatabaseInsert()
    {
        return $this->exportAsArrayExceptNull();
    }
}
