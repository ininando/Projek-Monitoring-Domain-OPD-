/*
 * Helper Chart.js bersama (Dashboard & Pemetaan).
 * Mengikuti design system: garis 2px, bar <= 24px dengan ujung membulat 4px,
 * grid tipis, teks memakai warna teks (bukan warna seri), label nilai di ujung bar.
 */
(function () {
    'use strict';

    const css = getComputedStyle(document.documentElement);
    const token = (nama, cadangan) => (css.getPropertyValue(nama) || '').trim() || cadangan;

    const WARNA = {
        primary: token('--ds-primary', '#0369A1'),
        teks: token('--ds-text', '#020617'),
        teksMuted: token('--ds-text-muted', '#475569'),
        grid: token('--ds-border', '#E2E8F0'),
        surface: token('--ds-surface', '#FFFFFF'),
        // Status: sudah divalidasi (validate_palette.js), selalu disertai label teks.
        critical: '#DC2626',
        warning: '#F59E0B',
        normal: '#16A34A',
        nonaktif: '#64748B',
    };

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    Chart.defaults.font.family = token('--ds-font-body', 'system-ui, sans-serif');
    Chart.defaults.font.size = 13;
    Chart.defaults.color = WARNA.teksMuted;
    Chart.defaults.borderColor = WARNA.grid;
    Chart.defaults.maintainAspectRatio = false;
    Chart.defaults.animation = reduceMotion ? false : { duration: 400 };
    Chart.defaults.plugins.legend.display = false;
    Object.assign(Chart.defaults.plugins.tooltip, {
        backgroundColor: '#0F172A',
        titleColor: '#FFFFFF',
        bodyColor: '#E2E8F0',
        padding: 10,
        cornerRadius: 6,
        displayColors: false,
    });

    /** Latar putih saat diekspor ke PNG (kanvas defaultnya transparan). */
    const latarPutih = {
        id: 'latarPutih',
        beforeDraw(chart) {
            const { ctx, width, height } = chart;
            ctx.save();
            ctx.globalCompositeOperation = 'destination-over';
            ctx.fillStyle = WARNA.surface;
            ctx.fillRect(0, 0, width, height);
            ctx.restore();
        },
    };

    /** Label nilai di ujung setiap bar (warna teks, bukan warna seri). */
    const labelNilai = {
        id: 'labelNilai',
        afterDatasetsDraw(chart) {
            if (chart.config.type !== 'bar') return;
            const { ctx } = chart;
            const horizontal = chart.options.indexAxis === 'y';
            ctx.save();
            ctx.fillStyle = WARNA.teks;
            ctx.font = `600 12px ${Chart.defaults.font.family}`;
            chart.getDatasetMeta(0).data.forEach((bar, i) => {
                const nilai = chart.data.datasets[0].data[i];
                if (horizontal) {
                    ctx.textAlign = 'left';
                    ctx.textBaseline = 'middle';
                    ctx.fillText(nilai, bar.x + 6, bar.y);
                } else {
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'bottom';
                    ctx.fillText(nilai, bar.x, bar.y - 4);
                }
            });
            ctx.restore();
        },
    };

    Chart.register(latarPutih, labelNilai);

    /** Pecah label panjang menjadi beberapa baris (Chart.js menerima array). */
    function bungkusLabel(teks, maks = 22) {
        const kata = String(teks).split(' ');
        const baris = [];
        let sekarang = '';
        kata.forEach((k) => {
            if ((sekarang + ' ' + k).trim().length > maks && sekarang) {
                baris.push(sekarang);
                sekarang = k;
            } else {
                sekarang = (sekarang + ' ' + k).trim();
            }
        });
        if (sekarang) baris.push(sekarang);
        return baris;
    }

    /** Opsi sumbu angka: mulai dari 0, bilangan bulat, grid tipis. */
    function sumbuNilai(extra = {}) {
        return Object.assign({
            beginAtZero: true,
            grace: '10%',
            ticks: { precision: 0 },
            grid: { color: WARNA.grid, lineWidth: 1 },
            border: { display: false },
        }, extra);
    }

    function sumbuKategori(extra = {}) {
        return Object.assign({
            grid: { display: false },
            border: { color: WARNA.grid },
            ticks: { color: WARNA.teks },
        }, extra);
    }

    /** Spesifikasi bar standar. */
    function datasetBar(data, warna, horizontal) {
        return {
            data,
            backgroundColor: warna,
            hoverBackgroundColor: warna,
            maxBarThickness: 24,
            borderRadius: 4,
            borderSkipped: 'start',
            categoryPercentage: 0.8,
            barPercentage: 0.9,
        };
    }

    /** Unduh chart sebagai PNG. */
    function unduhPng(chart, namaFile) {
        const tautan = document.createElement('a');
        tautan.href = chart.toBase64Image('image/png', 1);
        tautan.download = namaFile;
        document.body.appendChild(tautan);
        tautan.click();
        tautan.remove();
    }

    /** Isi tabel alternatif (aksesibilitas) dari data chart. */
    function isiTabel(tbody, baris) {
        tbody.replaceChildren(...baris.map((sel) => {
            const tr = document.createElement('tr');
            sel.forEach((isi, i) => {
                const td = document.createElement(i === 0 ? 'th' : 'td');
                if (i === 0) td.scope = 'row';
                else td.className = 'text-end tabular-nums';
                td.textContent = isi;
                tr.appendChild(td);
            });
            return tr;
        }));
    }

    window.DsChart = { WARNA, bungkusLabel, sumbuNilai, sumbuKategori, datasetBar, unduhPng, isiTabel };
})();
