$('body').addClass('js');

/* Safari */

$(function () {
    if (navigator.userAgent.indexOf('Safari') != -1 && 
    navigator.userAgent.indexOf('Chrome') == -1) {
        $("body").addClass("browser-safari");
    }
});

/* Zoom */

mediumZoom(document.querySelectorAll('.article figure > img, .micro figure > img'), {
    background: 'var(--color-background)'
})

/* Player */

var players = document.querySelectorAll('.player')

var loadPlayer = function (event) {
    var target = event.currentTarget
    var iframe = document.createElement('iframe')

    iframe.height = target.clientHeight
    iframe.width = target.clientWidth
    iframe.src = 'https://www.youtube.com/embed/' + target.dataset.videoId + '?autoplay=1&modestbranding=1&iv_load_policy=3&rel=0&showinfo=0'
    iframe.setAttribute('frameborder', 0)

    if (target.children.length) {
        target.replaceChild(iframe, target.firstElementChild)
    } else {
        target.appendChild(iframe)
    }
}

var config = {
    once: true
}

Array.from(players).forEach(function (player) {
    player.addEventListener('click', loadPlayer, config)
})

$('.player').on('click', function (evt) {
    $('.player > iframe').wrap('<div class="video"></div>');
});

/* Lzoad */

lozad('.lozad', {
    load: function(el) {
        el.src = el.dataset.src;
        el.onload = function() {
            el.classList.add('loaded')
        }
    }
}).observe()