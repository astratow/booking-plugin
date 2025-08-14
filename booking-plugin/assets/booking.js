document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('bp-book-btn');
    const slotsDiv = document.getElementById('bp-slots');

    btn.addEventListener('click', async () => {
        const res = await fetch(window.bpSlotsUrl);
        const slots = await res.json();
        slotsDiv.innerHTML = slots.map(s => 
            `<div>${s.slot_datetime}</div>`
        ).join('');
    });
});