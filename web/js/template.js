/**
 * jQuery template engine
 */
(function($) {
  $.fn.template = function(data) {
    return (new Function("data",
      "var p = [];" +
      "with(data) { p.push('" +
      this.html()
        .replace(/[\r\t\n]/g, " ")
        .split("<%").join("\t")
        .replace(/((^|%>)[^\t]*)'/g, "$1\r")
        .replace(/\t=(.*?)\s*%>/g, "',$1,'")
        .split("\t").join("');")
        .split("%>").join("p.push('")
        .split("\r").join("\\'") +
      "'); }" +
      "return p.join('');"
    ))(data);
  };
})(jQuery);