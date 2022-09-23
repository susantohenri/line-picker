jQuery(() => {
    jQuery.get(line_picker.users + '?cacheBreaker=' + Math.floor(Date.now() / 1000), users => {
        jQuery('p[id="line-picker"]').html(users)
    })
})