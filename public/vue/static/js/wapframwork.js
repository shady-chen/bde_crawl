(function () {
  var userAgent = navigator.userAgent
  var index = userAgent.indexOf('Android')
  if (index >= 0) {
    var androidVersion = parseFloat(userAgent.slice(index + 8))
    if (androidVersion < 5) {
      var html = document.documentElement
      var hW = html.getBoundingClientRect().width
      html.style.fontSize = hW / 7.5 + 'px'
    }
  }
})()
