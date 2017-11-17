import React from 'react';
import ReactDOM from 'react-dom';
import UserAssigner from '../UserAssigner';

ReactDOM.render(
  <UserAssigner name="메일 수신" apiUrl="/admin/recipient" />,
  document.getElementById('content'),
);
