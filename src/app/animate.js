export default $ => {

  let $container

  $.fn.isInViewport = function() {

    const elementTop = $(this).position().top
    const elementBottom = elementTop + $(this).height()
    const viewportTop = $container.scrollTop()
    const viewportBottom = viewportTop + $container.height()

    return ( 
      elementBottom > viewportTop + 30
      && 
      elementTop < viewportBottom - 30
    )
  };

  const $animateelements = $('figure img, p')

  const resizescroll = () => {

    const viewportTop = $container.scrollTop()
    const viewportBottom = viewportTop + $container.height()

    console.log(viewportTop)
    console.log(viewportBottom)
  
    $animateelements.each(
      function() {

        const $this = $(this)

        $this.isInViewport() ?
          $this.addClass('Visible')
          :
          $this.removeClass('Visible')
      }
    )
  }

  const waitContainer = setInterval(() => {

    $container = $('#page')
    
    if($container.length) {

      clearInterval(waitContainer)
  
      $(window)
      .on('resize', resizescroll)  

      $container
      .on('scroll', resizescroll)

      resizescroll()
    }
  }, 10)
}