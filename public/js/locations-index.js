/** 
 * for the admin/locations/index viewscript
 * 
 */
$(function(){
    var addButton = '<a href="#" class="btn btn-info btn-sm add-location">add new... <span class="fas fa-plus"></span></a>';
    $('#locations-list > li > a ').on("click",
    function(event){
        event.preventDefault();
        var that = this;
        var listItem = $(this).closest('li');
        var subList = listItem.children('ul');        
        if (subList.length) {
            /** @todo reconsider. fetch anew every time in case something 
             * was updated?
             */
            subList.toggle("slow");
        } else {            
            subList = $('<ul>').addClass('list-group subgroup').hide();
            items = [];            
            $.getJSON(that.href,function(data){               
                var url = that.href.replace('locations/type','locations/add/type');
                var thisButton = addButton.replace('#',url);
                
                if (! data.length) {
                    items[0] = $('<li>').addClass('list-group-item').html('none found<br>'+thisButton);
                } else {/** @todo  use map() for this instead?  */
                    for (i = 0; i < data.length; i++) {
                        var text = data[i].name;
                        if (data[i].parent) {
                            text += ", "+data[i].parent;
                        }
                        var href = 'locations/edit/' + data[i].id;
                        var html = $('<a/>').attr({href:href, title:"edit this location"}).text(text)
                        items[i] = $('<li>').html(html).addClass('list-group-item py-1');
                    }
                    items[++i]= $('<li>').html(thisButton).addClass('list-group-item'); 
                }

                subList.append(items);
                listItem.append(subList);
                subList.show("slow");
                
            });
        }
    });
});



