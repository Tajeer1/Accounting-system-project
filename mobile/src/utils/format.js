const CURRENCY_SYMBOL = 'ر.ع';
const CURRENCY_DECIMALS = 3;

export function formatMoney(amount, symbol = CURRENCY_SYMBOL, decimals = CURRENCY_DECIMALS) {
  const n = Number(amount) || 0;
  return n.toLocaleString('en', { minimumFractionDigits: decimals, maximumFractionDigits: decimals }) + ' ' + symbol;
}

export function shortMoney(amount, symbol = CURRENCY_SYMBOL) {
  const n = Number(amount) || 0;
  if (Math.abs(n) >= 1_000_000) return (n / 1_000_000).toFixed(1) + 'م ' + symbol;
  if (Math.abs(n) >= 1_000) return (n / 1_000).toFixed(1) + 'ك ' + symbol;
  return Math.round(n).toLocaleString('en') + ' ' + symbol;
}

export function formatDate(dateString) {
  if (!dateString) return '—';
  const d = new Date(dateString);
  const months = ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'];
  return `${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
}

export function formatDateShort(dateString) {
  if (!dateString) return '—';
  const d = new Date(dateString);
  const months = ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'];
  return `${d.getDate()} ${months[d.getMonth()]}`;
}
