import React from 'react';
import ReactDOM from 'react-dom';
import MyInfo from './MyInfo';
import * as api from '../../api/users';

ReactDOM.render(
  <MyInfo api={api} />,
  document.getElementById('content'),
);
