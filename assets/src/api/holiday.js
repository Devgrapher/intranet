import axios from 'axios';

const api = axios.create({
  baseURL: '/holidays',
});

export default {
  getByTeam: async (teamName, year) => {
    const { data: result } = await api.get('/', {
      params: {
        team: teamName,
        year,
      },
    });
    return result;
  },
};
