import axios from 'axios';

const getUsers = async () => {
  const { data: users } = await axios('/users/list');
  return users;
};

const dummy = '';

export {
  getUsers,
  dummy,
};
