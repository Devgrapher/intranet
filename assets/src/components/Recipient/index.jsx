import React from 'react';
import { getRecipient, updateRecipient } from '../../api/admin';
import UserAssigner from '../UserAssigner';

const Recipient = () => (
  <UserAssigner name="메일 수신" getApi={getRecipient} updateApi={updateRecipient} />

);

export default Recipient;
