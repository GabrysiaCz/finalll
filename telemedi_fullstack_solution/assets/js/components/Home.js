// assets/js/components/Home.js
import React, { useEffect, useState } from 'react';
import axios from 'axios';

const fmt = (n) => (n === null || n === undefined) ? '-' : Number(n).toFixed(4);

export default function Home() {
  const [rates, setRates] = useState({});         // { EUR: {buy,sell,mid,date}, ... }
  const [date, setDate] = useState(new Date().toISOString().slice(0,10));
  const [histories, setHistories] = useState({}); // { EUR: [{date,buy,sell,mid},...], USD: [...], ... }
  const [loadingRates, setLoadingRates] = useState(true);
  const [loadingHistory, setLoadingHistory] = useState(false);

  const loadRates = async (d = null) => {
    setLoadingRates(true);
    const resp = await axios.get('/api/rates', { params: d ? { date: d } : {} });
    setRates(resp.data.rates || {});
    setLoadingRates(false);
  };

  const loadHistories = async (codes, d) => {
    setLoadingHistory(true);
    try {
      const reqs = codes.map(code =>
        axios.get(`/api/rates/${code}/history`, { params: { date: d } })
             .then(r => [code, r.data.history || []])
             .catch(() => [code, []])
      );
      const results = await Promise.all(reqs);
      const map = {};
      results.forEach(([code, hist]) => { map[code] = hist; });
      setHistories(map);
    } finally {
      setLoadingHistory(false);
    }
  };

  useEffect(() => { loadRates(); }, []);


  useEffect(() => {
    const codes = Object.keys(rates);
    if (codes.length) loadHistories(codes, date);
  }, [rates, date]);

  const codes = Object.keys(rates); 

  return (
    <div className="container p-4">
      <h2>Kantor – kursy walut</h2>

      <div style={{display:'flex', gap:16, alignItems:'center', flexWrap:'wrap'}}>
        <label>Data:&nbsp;
          <input
            type="date"
            value={date}
            onChange={async e => {
              const d = e.target.value;
              setDate(d);
              await loadRates(d); 
            }}
          />
        </label>
      </div>

      {loadingRates ? <p>Ładowanie kursów…</p> : (
        <table border="1" cellPadding="8" style={{marginTop:16, width:'100%', borderCollapse:'collapse'}}>
          <thead>
            <tr><th>Waluta</th><th>Kupno</th><th>Sprzedaż</th><th>Śr. (NBP)</th></tr>
          </thead>
          <tbody>
            {codes.map(code => {
              const r = rates[code];
              return (
                <tr key={code}>
                  <td>{code}</td>
                  <td>{r.buy === null ? '-' : fmt(r.buy)}</td>
                  <td>{fmt(r.sell)}</td>
                  <td>{fmt(r.mid)}</td>
                </tr>
              );
            })}
          </tbody>
        </table>
      )}

      <h3 style={{marginTop:24}}>Historia 14 dni (do {date})</h3>
      {loadingHistory && <p>Ładowanie historii…</p>}

      {codes.map(code => {
        const hist = histories[code] || [];
        return (
          <div key={code} style={{marginTop:16}}>
            <h4 style={{margin:'8px 0'}}>{code}</h4>
            {hist.length === 0 ? <p>Brak danych.</p> : (
              <table border="1" cellPadding="6" style={{width:'100%', borderCollapse:'collapse'}}>
                <thead><tr><th>Data</th><th>Kupno</th><th>Sprzedaż</th><th>Śr.</th></tr></thead>
                <tbody>
                  {hist.map(row => (
                    <tr key={`${code}-${row.date}`}>
                      <td>{row.date}</td>
                      <td>{row.buy === null ? '-' : fmt(row.buy)}</td>
                      <td>{fmt(row.sell)}</td>
                      <td>{fmt(row.mid)}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            )}
          </div>
        );
      })}
    </div>
  );
}
