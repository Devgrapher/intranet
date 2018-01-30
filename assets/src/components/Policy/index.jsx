import React from 'react';
import { getPolicy, updatePolicy } from '../../api/admin';
import UserAssigner from '../UserAssigner';

const Policy = () => (
  <UserAssigner name="권한" getApi={getPolicy} updateApi={updatePolicy} />
);

export default Policy;
