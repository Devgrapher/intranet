import axios from 'axios';
import _ from 'lodash';

const api = axios.create({
  baseURL: '/payments',
});

export default {
  add: async (userId, data, files) => {
    const formData = new FormData();
    _.forEach(data, (value, key) => formData.append(key, value));
    _.forEach(files, file => formData.append('files[]', file));
    const { data: result } = await api.post(`/uid/${userId}`, formData);
    return result;
  },

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

  addAttachmentFiles: async (files, paymentId) => {
    const data = new FormData();
    data.append('paymentid', paymentId);
    _.forEach(files, file => data.append('files[]', file));
    const { data: result } = await api.post('/file_upload', data);
    return result;
  },

  removeAttachmentFile: async (fileId) => {
    const { data: result } = await api.delete(`/file/${fileId}`);
    return result;
  },
};
