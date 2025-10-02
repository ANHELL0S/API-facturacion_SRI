const paymentMethodMap = {
  'sin utilizacion del sistema financiero': '01',
  'efectivo': '01',
  'compensacion de deudas': '15',
  'tarjeta de debito': '16',
  'dinero electronico': '17',
  'tarjeta prepago': '18',
  'tarjeta de credito': '19',
  'otros con utilizacion del sistema financiero': '20',
  'endoso de titulos': '21',
};

export const paymentMethodOptions = [
    { value: '01', text: 'Sin Utilización del Sistema Financiero' },
    { value: '15', text: 'Compensación de Deudas' },
    { value: '16', text: 'Tarjeta de Débito' },
    { value: '17', text: 'Dinero Electrónico' },
    { value: '18', text: 'Tarjeta Prepago' },
    { value: '19', text: 'Tarjeta de Crédito' },
    { value: '20', text: 'Otros con Utilización del Sistema Financiero' },
    { value: '21', text: 'Endoso de Títulos' },
];

export const parsePaymentMethods = (paymentString, totalAmount) => {
  if (!paymentString || String(paymentString).trim() === '') {
    return [{ formaPago: '01', total: totalAmount }];
  }

  const methodName = String(paymentString).trim();
  const normalizedMethodName = methodName.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
  const formaPago = paymentMethodMap[normalizedMethodName];

  if (!formaPago) {
    throw new Error(`Método de pago no reconocido: "${methodName}"`);
  }

  return [{ formaPago, total: totalAmount }];
};
