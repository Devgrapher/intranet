import React from 'react';
import ReactDOM from 'react-dom';
import Me from './Me';
import * as api from '../../api/users';

ReactDOM.render(
  <Me api={api} />,
  document.getElementById('content'),
);
