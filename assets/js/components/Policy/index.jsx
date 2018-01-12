import React from 'react';
import ReactDOM from 'react-dom';
import { getPolicy, updatePolicy } from '../../api/admin';
import UserAssigner from '../UserAssigner';

ReactDOM.render(
  <UserAssigner name="권한" getApi={getPolicy} updateApi={updatePolicy} />,
  document.getElementById('content'),
);
