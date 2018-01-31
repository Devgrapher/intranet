import axios from 'axios';

const getPolicy = async () => {
  const { data: policies } = await axios.get('/admin/policy');
  return policies;
};

const updatePolicy = async (assigned) => {
  const { data: result } = await axios.post('/admin/policy', { assigned });
  return result;
};

const getRecipient = async () => {
  const { data: recipients } = await axios.get('/admin/recipient');
  return recipients;
};

const updateRecipient = async (assigned) => {
  const { data: result } = await axios.post('/admin/recipient', { assigned });
  return result;
};

const addRoomSection = async (type, name, isVisible) => {
  const { data: result } = await axios.post('/admin/room', {
    type,
    name,
    is_visible: isVisible,
  });

  return result;
};

const updateRoomSection = async (roomId, type, name, isVisible) => {
  const { data: result } = await axios.post(`/admin/room/${roomId}`, {
    type,
    name,
    is_visible: isVisible,
  });

  return result;
};

const deleteRoomSection = async (roomId) => {
  const { data: result } = await axios.delete(`/admin/room/${roomId}`);
  return result;
};

const getEventGroups = async () => {
  const { data: eventGroups } = await axios.get('/admin/event_group');
  return eventGroups;
};

const addEventGroup = async (uid, roomId, fromDate, toDate, daysOfWeek, fromTime, toTime, desc) => {
  const { data: result } = await axios.post('/admin/event_group', {
    uid,
    room_id: roomId,
    from_date: fromDate,
    to_date: toDate,
    days_of_week: daysOfWeek,
    from_time: fromTime,
    to_time: toTime,
    desc,
  });

  return result;
};

const updateEventGroup = async (eventGroupId, uid, roomId, fromDate, toDate, daysOfWeek, fromTime, toTime, desc) => {
  const { data: result } = await axios.post(`/admin/event_group/${eventGroupId}`, {
    uid,
    room_id: roomId,
    from_date: fromDate,
    to_date: toDate,
    days_of_week: daysOfWeek,
    from_time: fromTime,
    to_time: toTime,
    desc,
  });

  return result;
};

const deleteEventGroup = async (eventGroupID) => {
  const { data: result } = await axios.delete(`/admin/event_group/${eventGroupID}`);
  return result;
};

export {
  getPolicy,
  updatePolicy,
  getRecipient,
  updateRecipient,
  addRoomSection,
  updateRoomSection,
  deleteRoomSection,
  getEventGroups,
  addEventGroup,
  updateEventGroup,
  deleteEventGroup,
};
