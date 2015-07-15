$(function() {
    var opts = {
	animateScale: true,
	segmentShowStroke: false
    };
    var wtChart = new Chart(getContext('gClassWt')).Pie(jsonWt, opts);
    var cpuChart = new Chart(getContext('gClassCpu')).Pie(jsonCpu, opts);
    var muChart = new Chart(getContext('gClassMu')).Pie(jsonMu, opts);
    var pmuChart = new Chart(getContext('gClassPmu')).Pie(jsonPmu, opts);
});