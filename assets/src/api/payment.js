import axios from 'axios';

const api = axios.create({
  baseURL: '/payments',
});

export default {
  get: async (...args) => {
    const { data: result } = await api.get(...args);
    return result;
  },

  update: async (paymentId, key, value) => {
    const { data: result } = await api.patch(`/paymentid/${paymentId}`, {
      key,
      value,
    });
    return result;
  },

  remove: async (paymentId) => {
    const { data: result } = await api.delete(`/paymentid/${paymentId}`);
    return result;
  },

  download: async (...args) => {
    const [path, options] = args;
    const response = await api.get(path, {
      responseType: 'blob',
      ...options,
    });

    const disposition = response.headers['content-disposition'];
    const matches = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/.exec(disposition);
    const filename = matches[1].replace(/['"]/g, '');

    const link = document.createElement('a');
    link.href = window.URL.createObjectURL(new Blob([response.data]));
    link.setAttribute('download', filename);
    link.click();
  },

  addAttachmentFile: async (file, paymentId) => {
    const data = new FormData();
    data.append('paymentid', paymentId);
    data.append('files[]', file);
    const { data: result } = await api.post('/file_upload', data);
    return result;
  },

  removeAttachmentFile: async (fileId) => {
    const { data: result } = await api.delete(`/file/${fileId}`);
    return result;
  },
};
