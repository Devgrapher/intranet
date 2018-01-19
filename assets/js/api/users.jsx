import axios from 'axios';

const getMe = async () => {
  const { data: me } = await axios.get('/users/me');
  return me;
};

const updateImage = async (uid, file) => {
  const data = new FormData();
  data.append('uid', uid);
  data.append('files[]', file);
  const { data: imageUrl } = await axios.post('/users/image_upload', data);
  return imageUrl;
};

const updateUser = async (pk, name, value) => {
  const { data: result } = await axios.post('/users/edit', {
    pk,
    name,
    value,
  });
  return result;
};

const getUsers = async () => {
  const { data: users } = await axios('/users/list');
  return users;
};

export {
  getMe,
  getUsers,
  updateImage,
  updateUser,
};
