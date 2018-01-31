import axios from 'axios';

const getSections = async (type) => {
  const { data: sections } = await axios(`/rooms/section?type=${type || 'default'}`);
  return sections;
};

const addEvent = async (desc, from, to, roomId) => {
  const { data: result } = await axios.post('/rooms/event', {
    desc,
    from,
    to,
    room_id: roomId,
  });
  return result;
};

const updateEvent = async (eventId, desc, from, to, roomId) => {
  const { data: result } = await axios.post(`/rooms/event/${eventId}`, {
    desc,
    from,
    to,
    room_id: roomId,
  });

  return result;
};

const deleteEvent = async (eventId) => {
  const { data: result } = await axios.delete(`/rooms/event/${eventId}`);
  return result;
};

export {
  getSections,
  addEvent,
  updateEvent,
  deleteEvent,
};
