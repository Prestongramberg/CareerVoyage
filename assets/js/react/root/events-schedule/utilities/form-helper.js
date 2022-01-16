export function getArrayOfTimes(interval = 30) {
    let times = [], // time array
        st = 0, // start time
        ap = ['am', 'pm']; // AM-PM

    for (let i = 0; st < 24 * 60; i++) {
        let hh = Math.floor(st / 60);
        let mm = (st % 60);
        times[i] = ("" + ((hh === 12) ? 12 : (hh % 12 === 0 ? "12" : hh % 12))).slice(-2) + ':' + ("0" + mm).slice(-2) + ' ' + ap[Math.floor(hh / 12)];
        st = st + interval;
    }

    return times;
}