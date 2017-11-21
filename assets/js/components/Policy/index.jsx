import React from 'react';
import ReactDOM from 'react-dom';
import UserAssigner from '../UserAssigner';

ReactDOM.render(
  <UserAssigner name="권한" apiUrl="/admin/policy" />,
  document.getElementById('content'),
);
