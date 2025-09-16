(function ($) {
    'use strict';

    function getChartColorsArray(selector) {
        var element = $(selector);
        if (!element.length) {
            return [];
        }

        var colors = element.attr('data-colors');
        if (!colors) {
            return [];
        }

        try {
            colors = JSON.parse(colors);
        } catch (error) {
            return [];
        }

        return colors.map(function (value) {
            var newValue = value.replace(' ', '');

            if (newValue.indexOf('--') === -1) {
                return newValue;
            }

            var computedColor = getComputedStyle(document.documentElement).getPropertyValue(newValue);
            return computedColor || value;
        });
    }

    function initSparklineChart(selector, seriesData) {
        var element = document.querySelector(selector);

        if (!element || typeof ApexCharts === 'undefined') {
            return;
        }

        var colors = getChartColorsArray(selector);

        if (element._apexChart) {
            element._apexChart.destroy();
        }

        var options = {
            series: [{ data: seriesData }],
            chart: {
                type: 'line',
                height: 50,
                sparkline: { enabled: true }
            },
            colors: colors,
            stroke: { curve: 'smooth', width: 2 },
            tooltip: {
                fixed: { enabled: false },
                x: { show: false },
                y: { title: { formatter: function () { return ''; } } },
                marker: { show: false }
            }
        };

        var chart = new ApexCharts(element, options);
        chart.render();
        element._apexChart = chart;
    }

    function initMiniCharts() {
        ['#mini-chart1', '#mini-chart2', '#mini-chart3', '#mini-chart4'].forEach(function (selector) {
            var element = document.querySelector(selector);

            if (!element) {
                return;
            }

            var seriesAttr = element.getAttribute('data-series');
            if (!seriesAttr) {
                return;
            }

            var seriesData;

            try {
                seriesData = JSON.parse(seriesAttr);
            } catch (error) {
                seriesData = [];
            }

            if (!Array.isArray(seriesData) || !seriesData.length) {
                return;
            }

            initSparklineChart(selector, seriesData);
        });
    }

    function initMonthlyEarningsChart() {
        var element = document.querySelector('#monthly-earnings-chart');

        if (!element || typeof ApexCharts === 'undefined') {
            return;
        }

        var seriesAttr = element.getAttribute('data-series');
        var labelsAttr = element.getAttribute('data-labels');
        var currency = element.getAttribute('data-currency') || '';

        var seriesData = [];
        var labels = [];

        if (seriesAttr) {
            try {
                seriesData = JSON.parse(seriesAttr);
            } catch (error) {
                seriesData = [];
            }
        }

        if (labelsAttr) {
            try {
                labels = JSON.parse(labelsAttr);
            } catch (error) {
                labels = [];
            }
        }

        if (!seriesData.length) {
            element.innerHTML = '<div class="text-muted text-center py-5">No earnings data available.</div>';
            return;
        }

        var colors = getChartColorsArray('#monthly-earnings-chart');

        if (element._apexChart) {
            element._apexChart.destroy();
        }

        var chart = new ApexCharts(element, {
            series: [{
                name: 'Earnings',
                data: seriesData
            }],
            chart: {
                type: 'area',
                height: 320,
                toolbar: { show: false }
            },
            stroke: { curve: 'smooth', width: 3 },
            colors: colors.length ? colors : undefined,
            dataLabels: { enabled: false },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 0.6,
                    opacityFrom: 0.45,
                    opacityTo: 0.05,
                    stops: [0, 90, 100]
                }
            },
            markers: { size: 4 },
            xaxis: {
                categories: labels,
                labels: { style: { fontWeight: 500 } }
            },
            yaxis: {
                labels: {
                    formatter: function (value) {
                        return currency + value.toFixed(2);
                    }
                }
            },
            tooltip: {
                y: {
                    formatter: function (value) {
                        return currency + value.toFixed(2);
                    }
                }
            }
        });

        chart.render();
        element._apexChart = chart;
    }

    function renderLineChart(selector) {
        var element = document.querySelector(selector);

        if (!element || typeof ApexCharts === 'undefined') {
            return;
        }

        var labelsAttr = element.getAttribute('data-labels');
        var seriesAttr = element.getAttribute('data-series');
        var colors = getChartColorsArray(selector);

        var labels = [];
        var series = [];

        if (labelsAttr) {
            try {
                labels = JSON.parse(labelsAttr);
            } catch (error) {
                labels = [];
            }
        }

        if (seriesAttr) {
            try {
                series = JSON.parse(seriesAttr);
            } catch (error) {
                series = [];
            }
        }

        if (!series.length) {
            element.innerHTML = '<div class="text-muted text-center py-5">No data available.</div>';
            return;
        }

        if (element._apexChart) {
            element._apexChart.destroy();
        }

        var chart = new ApexCharts(element, {
            series: series,
            chart: { type: 'line', height: 320, toolbar: { show: false } },
            colors: colors.length ? colors : undefined,
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 3 },
            markers: { size: 4 },
            xaxis: { categories: labels },
            tooltip: { shared: true }
        });

        chart.render();
        element._apexChart = chart;
    }

    function initNewUsersChart() {
        ['#new-users-chart', '#report-new-users-chart'].forEach(renderLineChart);
    }

    function initWalletBalance() {
        var selector = '#wallet-balance';
        var element = document.querySelector(selector);

        if (!element || typeof ApexCharts === 'undefined') {
            return;
        }

        var colors = getChartColorsArray(selector);

        var chart = new ApexCharts(element, {
            series: [35, 70, 15],
            chart: { width: 227, height: 227, type: 'pie' },
            labels: ['Ethereum', 'Bitcoin', 'Litecoin'],
            colors: colors,
            stroke: { width: 0 },
            legend: { show: false },
            responsive: [{ breakpoint: 480, options: { chart: { width: 200 } } }]
        });

        chart.render();
    }

    function initInvestedOverview() {
        var selector = '#invested-overview';
        var element = document.querySelector(selector);

        if (!element || typeof ApexCharts === 'undefined') {
            return;
        }

        var colors = getChartColorsArray(selector);

        var chart = new ApexCharts(element, {
            chart: { height: 270, type: 'radialBar', offsetY: -10 },
            plotOptions: {
                radialBar: {
                    startAngle: -130,
                    endAngle: 130,
                    dataLabels: {
                        name: { show: false },
                        value: {
                            offsetY: 10,
                            fontSize: '18px',
                            color: undefined,
                            formatter: function (value) { return value + '%'; }
                        }
                    }
                }
            },
            colors: [colors[0]],
            fill: {
                type: 'gradient',
                gradient: {
                    shade: 'dark',
                    type: 'horizontal',
                    gradientToColors: [colors[1]],
                    shadeIntensity: 0.15,
                    inverseColors: false,
                    opacityFrom: 1,
                    opacityTo: 1,
                    stops: [20, 60]
                }
            },
            stroke: { dashArray: 4 },
            legend: { show: false },
            series: [80],
            labels: ['Series A']
        });

        chart.render();
    }

    function renderStackedBarChart(selector) {
        var element = document.querySelector(selector);

        if (!element || typeof ApexCharts === 'undefined') {
            return;
        }

        var labelsAttr = element.getAttribute('data-labels');
        var seriesAttr = element.getAttribute('data-series');
        var colors = getChartColorsArray(selector);

        var labels = [];
        var series = [];

        if (labelsAttr) {
            try {
                labels = JSON.parse(labelsAttr);
            } catch (error) {
                labels = [];
            }
        }

        if (seriesAttr) {
            try {
                series = JSON.parse(seriesAttr);
            } catch (error) {
                series = [];
            }
        }

        if (!series.length) {
            element.innerHTML = '<div class="text-muted text-center py-5">No data available.</div>';
            return;
        }

        if (element._apexChart) {
            element._apexChart.destroy();
        }

        var chart = new ApexCharts(element, {
            series: series,
            chart: { type: 'bar', height: 320, stacked: true, toolbar: { show: false } },
            colors: colors.length ? colors : undefined,
            dataLabels: { enabled: false },
            plotOptions: { bar: { columnWidth: '45%', borderRadius: 6 } },
            xaxis: { categories: labels },
            yaxis: { labels: { formatter: function (value) { return value.toFixed(0); } } },
            tooltip: { shared: true },
            legend: { position: 'top' },
            grid: { padding: { left: 10, right: 10 } }
        });

        chart.render();
        element._apexChart = chart;
    }

    function initTransactionOverviewChart() {
        ['#transaction-overview-chart', '#report-transaction-chart'].forEach(renderStackedBarChart);
    }

    function renderDonutChart(selector) {
        var element = document.querySelector(selector);

        if (!element || typeof ApexCharts === 'undefined') {
            return;
        }

        var labelsAttr = element.getAttribute('data-labels');
        var seriesAttr = element.getAttribute('data-series');
        var colors = getChartColorsArray(selector);

        var labels = [];
        var series = [];

        if (labelsAttr) {
            try {
                labels = JSON.parse(labelsAttr);
            } catch (error) {
                labels = [];
            }
        }

        if (seriesAttr) {
            try {
                series = JSON.parse(seriesAttr);
            } catch (error) {
                series = [];
            }
        }

        if (!series.length) {
            element.innerHTML = '<div class="text-muted text-center py-5">No data available.</div>';
            return;
        }

        if (element._apexChart) {
            element._apexChart.destroy();
        }

        var chart = new ApexCharts(element, {
            series: series,
            chart: { type: 'donut', height: 320 },
            labels: labels,
            colors: colors.length ? colors : undefined,
            legend: { position: 'bottom' },
            dataLabels: { enabled: true },
            stroke: { width: 0 }
        });

        chart.render();
        element._apexChart = chart;
    }

    function initRevenueSplitCharts() {
        ['#revenue-split-chart'].forEach(renderDonutChart);
    }

    function initMarketOverview() {
        var selector = '#market-overview';
        var element = document.querySelector(selector);

        if (!element || typeof ApexCharts === 'undefined') {
            return;
        }

        var colors = getChartColorsArray(selector);

        var chart = new ApexCharts(element, {
            series: [
                { name: 'Profit', data: [12.45, 16.2, 8.9, 11.42, 12.6, 18.1, 18.2, 14.16, 11.1, 8.09, 16.34, 12.88] },
                { name: 'Loss', data: [-11.45, -15.42, -7.9, -12.42, -12.6, -18.1, -18.2, -14.16, -11.1, -7.09, -15.34, -11.88] }
            ],
            chart: { type: 'bar', height: 400, stacked: true, toolbar: { show: false } },
            plotOptions: { bar: { columnWidth: '20%' } },
            colors: colors,
            fill: { opacity: 1 },
            dataLabels: { enabled: false },
            legend: { show: false },
            yaxis: {
                labels: {
                    formatter: function (value) {
                        return value.toFixed(0) + '%';
                    }
                }
            },
            xaxis: {
                categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                labels: { rotate: -90 }
            }
        });

        chart.render();
    }

    function initSalesByLocations() {
        var selector = '#sales-by-locations';
        var element = $(selector);

        if (!element.length || typeof element.vectorMap !== 'function') {
            return;
        }

        var colors = getChartColorsArray(selector);

        element.vectorMap({
            map: 'world_mill_en',
            normalizeFunction: 'polynomial',
            hoverOpacity: 0.7,
            hoverColor: false,
            regionStyle: { initial: { fill: '#e9e9ef' } },
            markerStyle: {
                initial: {
                    r: 9,
                    fill: colors,
                    'fill-opacity': 0.9,
                    stroke: '#fff',
                    'stroke-width': 7,
                    'stroke-opacity': 0.4
                },
                hover: {
                    stroke: '#fff',
                    'fill-opacity': 1,
                    'stroke-width': 1.5
                }
            },
            backgroundColor: 'transparent',
            markers: [
                { latLng: [41.9, 12.45], name: 'USA' },
                { latLng: [12.05, -61.75], name: 'Russia' },
                { latLng: [1.3, 103.8], name: 'Australia' }
            ]
        });
    }

    function initialiseCharts() {
        initMiniCharts();
        initMonthlyEarningsChart();
        initNewUsersChart();
        initTransactionOverviewChart();
        initRevenueSplitCharts();
        initWalletBalance();
        initInvestedOverview();
        initMarketOverview();
        initSalesByLocations();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialiseCharts);
    } else {
        initialiseCharts();
    }

    document.addEventListener('livewire:navigated', initialiseCharts);
})(window.jQuery);
