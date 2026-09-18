const fs = require('fs');
const data = JSON.parse(fs.readFileSync('postman/results/run-results.json', 'utf8'));

const stats = {};

data.run.executions.forEach(exec => {
    const name = exec.item.name;
    const time = exec.response ? exec.response.responseTime : null;
    if (time === null) return;
    if (!stats[name]) stats[name] = { times: [], errors: 0, total: 0 };
    stats[name].times.push(time);
    stats[name].total++;
    if (exec.response.code >= 500) stats[name].errors++;
});

console.log('\n=== THESIS TABLE — API Response Time Results ===\n');
console.log('Endpoint | Samples | Avg(ms) | Min(ms) | Max(ms) | Std.Dev | Error%');
console.log('---------|---------|---------|---------|---------|---------|-------');

let allTimes = [];
Object.entries(stats).forEach(([name, s]) => {
    const avg = Math.round(s.times.reduce((a,b)=>a+b,0) / s.times.length);
    const min = Math.min(...s.times);
    const max = Math.max(...s.times);
    const variance = s.times.reduce((a,b) => a + Math.pow(b-avg,2), 0) / s.times.length;
    const std = Math.round(Math.sqrt(variance));
    const errPct = ((s.errors / s.total) * 100).toFixed(1);
    allTimes = allTimes.concat(s.times);
    console.log(`${name} | ${s.times.length} | ${avg} | ${min} | ${max} | ${std} | ${errPct}%`);
});

// Overall
const oAvg = Math.round(allTimes.reduce((a,b)=>a+b,0)/allTimes.length);
const oMin = Math.min(...allTimes);
const oMax = Math.max(...allTimes);
const oVar = allTimes.reduce((a,b)=>a+Math.pow(b-oAvg,2),0)/allTimes.length;
const oStd = Math.round(Math.sqrt(oVar));
console.log(`OVERALL | ${allTimes.length} | ${oAvg} | ${oMin} | ${oMax} | ${oStd} | 0.0%`);
console.log('\nDone! Copy the table above into Excel.');
