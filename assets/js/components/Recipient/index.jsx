import React from 'react';
import ReactDOM from 'react-dom';
import { getRecipient, updateRecipient } from '../../api/admin';
import UserAssigner from '../UserAssigner';

ReactDOM.render(
  <UserAssigner name="메일 수신" getApi={getRecipient} updateApi={updateRecipient} />,
  document.getElementById('content'),
);
