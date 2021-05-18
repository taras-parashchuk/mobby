import {Line} from 'vue-chartjs'

export default {
    extends: Line,
    props: ['height', 'color', 'data', 'labels'],
    data: () => ({
        chartdata: {
            labels: [],
            datasets: [{
                backgroundColor: '',
                borderColor: '',
                data: [],
                fill: false,
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            legend: {
                display: false
            },
            title: {
                display: false
            },
            tooltips: {
                mode: 'index',
                intersect: false,
            },
            hover: {
                mode: 'nearest',
                intersect: true
            },
            scales: {
                xAxes: [{
                    display: true,
                    scaleLabel: {
                        display: true
                    },
                    ticks: {
                        fontColor: "#748aa1",
                        fontSize: 12
                    },
                    gridLines: {
                        color: 'transparent'
                    }
                }],
                yAxes: [{
                    display: true,
                    scaleLabel: {
                        display: true
                    },
                    ticks: {
                        fontColor: "#748aa1",
                        fontSize: 12,
                        stepSize: 10
                    },
                    gridLines: {
                        color: '#2d2e42',
                    }
                }]
            },
            cubicInterpolationMode: 'monotone'
        }
    }),
    created() {

        this.chartdata.labels = this.labels;

        for (let i in  this.chartdata.datasets) {

            this.chartdata.datasets[i].backgroundColor = this.color;
            this.chartdata.datasets[i].borderColor = this.color;
            this.chartdata.datasets[i].data = this.data[i];

        }
    },
    mounted() {
        this.renderChart(this.chartdata, this.options)
    }
}