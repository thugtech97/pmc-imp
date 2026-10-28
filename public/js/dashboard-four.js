$(function(){
  'use strict'

  $.plot('#flotChart1', [{
    data: df2,
    color: '#65e0e0'
  },{
    data: df1,
    color: '#69b2f8'
  },{
    data: df3,
    color: '#0168fa',
    lines: {
      show: true,
      lineWidth: 1.5
    },
    bars: { show: false }
  }], {
    series: {
      shadowSize: 0,
      bars: {
        show: true,
        lineWidth: 0,
        barWidth: .5,
        fill: 1
      }
    },
    grid: {
      color: '#c0ccda',
      borderWidth: 0,
      labelMargin: 10
    },
    yaxis: {
      show: true,
      max: 90,
      tickSize: 15
    },
    xaxis: {
      color: 'transparent',
      show: true,
      max: 37,
      //ticks: [[0,'Jan 22'],[5,'Feb 22'],[10,'Mar 12'],[15,'Apr 22'],[20,'May 22'],[25,'Jun 22'],[30,'July 22'],[35,'Aug 22']]
      ticks: [[0,'Jan 22'],[3,'Feb 22'],[6,'Mar 12'],[9,'Apr 22'],[12,'May 22'],[15,'Jun 22'],[18,'July 22'],[21,'Aug 22'],[24,'Sep 22'],[27,'Oct 22'],[30,'Nov 22'],[33,'Dec 22'],[36,'Jan 23']]
    }
  });

  // Donut chart
  $('.peity-donut').peity('donut');


  $.plot('#flotChart2', [{
    data: df3,
    color: '#0168fa',
    curvedLines: { apply: true }
  }], {
    series: {
      shadowSize: 0,
      lines: {
        show: true,
        lineWidth: 1.5,
        fill: .05
      }
    },
    grid: {
      borderWidth: 0,
      labelMargin: 0
    },
    yaxis: {
      show: false,
      max: 55
    },
    xaxis: {
      show: true,
      color: 'rgba(131,146,165,.08)',
      min: 48,
      max: 102,
      tickSize: 5
    }
  });

  // Horizontal bar chart
  var ctx2 = document.getElementById('chartBar2').getContext('2d');
  var chartBar = new Chart(ctx2, {
    type: 'horizontalBar',
    data: {
      labels: ['ASPERA, KRYSTALLE', 'RODRIGO, ZOSIMO', 'FUSINGAN, KATHERINE', 'MADRAZO, ERIC', 'TALAUGON, JAY', 'TOYOGON, ERICKA'],
      datasets: [{
        data: [64, 53, 50, 79, 33, 48],
        backgroundColor: ['#65e0e0', '#69b2f8','#6fd39b','#f77eb9','#fdb16d','#c693f9']
      },{
        data: [11, 5, 23, 36, 16, 5],
        backgroundColor: '#e5e9f2'
      }]
    },
    options: {
      maintainAspectRatio: false,
      responsive: true,
      legend: {
        display: false,
        labels: {
          display: false
        }
      },
      layout: {
        padding: {
          left: 5
        }
      },
      scales: {
        yAxes: [{
          gridLines: {
            display: false
          },
          barPercentage: 0.5,
          ticks: {
            beginAtZero:true,
            fontSize: 13,
            fontColor: '#182b49',
            fontFamily: 'IBM Plex Sans'
          }
        }],
        xAxes: [{
          gridLines: {
            color: '#e5e9f2'
          },
          ticks: {
            beginAtZero: true,
            fontSize: 10,
            fontColor: '#182b49',
            max: 100
          }
        }]
      }
    }
  });




  window.darkMode = function(){
    $('.btn-white').addClass('btn-dark').removeClass('btn-white');

    //console.log(chartBar.options.scales);
    chartBar.options.scales.xAxes[0].gridLines.color = 'rgba(255,255,255,.04)';
    chartBar.options.scales.xAxes[0].ticks.minor.fontColor = '#97a3b9';
    chartBar.options.scales.yAxes[0].ticks.minor.fontColor = '#97a3b9';
    chartBar.update();
  }

  window.lightMode = function() {
    $('.btn-dark').addClass('btn-white').removeClass('btn-dark');
  }

  var hasMode = Cookies.get('df-mode');
  if(hasMode === 'dark') {
    darkMode();
  } else {
    lightMode();
  }


})
