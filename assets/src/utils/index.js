import moment from 'moment/moment';

export const formatDate = dateString => (
  moment(dateString).format('YYYY-MM-DD')
);

export const parseNumber = s => (
  parseFloat(s.replace(/[^0-9|^.]/g, ''))
);

export const formatCurrency = number => (
  new Intl.NumberFormat().format(number)
);
