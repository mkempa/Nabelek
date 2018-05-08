
function clrReplyField(element) {
    var $input = element.find('div.input');
    $input.children('input').val('');
    $input.children('textarea').val('');
    $("#parent-id").val('null');
    $('#reply-fields').hide();
}

/**
 * 
 * @param {type} url
 * @param {type} indata serialised data
 * @param {type} referer element form sending request
 * @returns {undefined}
 */

function sendPostTo(url, indata, referer) {
    $.post(url, indata, function (data) {
        if (data) {
            alert("Your comment has been submitted and is waiting for approval");
            clrReplyField(referer);
        }
    });
}

$(document).ready(function () {

    /* Comments */
    var $rf = $('#reply-fields');
    $rf.hide();
    $('#add-new-comment').click(function (e) {
        $rf.find('input#parent-id').val('null');
        $rf.appendTo($('div#comments-body'));
        $(this).hide();
        $rf.show();
    });
    
    $('.reply').click(function (e) {
        e.preventDefault();
        $rf.hide().detach();
        var repToId = $(this).parents("div.comment").find("input.comment-id").val();
        $rf.find('input#parent-id').val(repToId);
        $(this).parent('div').append($rf);
        $(this).hide();
        $(this).siblings('.close-reply').show();
        $('#add-new-comment').show();
        $rf.show();
    });

    $('.close-reply').hide();
    $('.close-reply').click(function (e) {
        e.preventDefault();
        $rf.hide().detach().appendTo($('#add-new-comment'));
        $(this).siblings('.reply').show();
        $(this).hide();
    });
    
});