import http from 'k6/http';
import { check } from 'k6';

export const options = {
  vus: 20,
  iterations: 20,
};

const TOKEN = '22|4s9SESNtUFRAtrcW5o2hXFpBeuB43JG0zrJMcQrOb4157282';

export default function () {
  const res = http.post(
    'http://localhost:8080/api/reservas',
    JSON.stringify({
      servicio_id: 10,
      fecha: '2026-06-03',
      hora_inicio: '16:30'
    }),
    {
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${TOKEN}`,
      },
    }
  );
  
  console.log(
    `status=${res.status} body=${res.body}`
  );

  check(res, {
    'no es 401': (r) => r.status !== 401,
  });
}