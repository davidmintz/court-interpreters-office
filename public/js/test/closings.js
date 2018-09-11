
QUnit.test( "assert.async() test", function( assert ) {
    assert.ok($("a.closing-link").length > 0, "per-year links exist");
    assert.ok($("a.closing-link").first().siblings("ul").length,"ul for per-year closings exist");

    var done = assert.async();
    $("a.closing-link").first().trigger("click");
    var ul = $("a.closing-link").first().siblings("ul")
    assert.ok(ul.children().length == 0,"no sub-list items yet")
    setTimeout(function(){
        assert.ok(ul.children("li").length > 0, "sub-list items have been loaded");
        var link = ul.find("li a").first();
        var url  = link.attr("href").replace("/test","");
        link.attr({href:url}).trigger("click");
        //console.log(url.replace("/test",""));
        done();
    },1000);
});
