$(function() {
    var opts = {
	animateScale:true,
	segmentShowStroke: false
    };
    var wtChart = new Chart(getContext('gRunWt')).Pie(jsonWt, opts);
    var cpuChart = new Chart(getContext('gRunCpu')).Pie(jsonCpu, opts);
    var muChart = new Chart(getContext('gRunMu')).Pie(jsonMu, opts);
    var pmuChart = new Chart(getContext('gRunPmu')).Pie(jsonPmu, opts);
});