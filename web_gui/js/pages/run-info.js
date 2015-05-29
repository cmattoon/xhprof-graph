function getcontext(el) {
    var e, c = null;
    e = document.getElementById(el);
    if (e) c = e.getContext('2d');
    return c;
}
$(function() {
    var opts = {
	animateScale:true,
	segmentShowStroke: false
    };
    var wtChart = new Chart(getcontext('gRunWt')).Pie(jsonWt, opts);
    var cpuChart = new Chart(getcontext('gRunCpu')).Pie(jsonCpu, opts);
    var muChart = new Chart(getcontext('gRunMu')).Pie(jsonMu, opts);
    var pmuChart = new Chart(getcontext('gRunPmu')).Pie(jsonPmu, opts);
    console.log(pc=pieChart);
});