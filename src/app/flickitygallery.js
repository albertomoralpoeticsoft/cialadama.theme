// https://github.com/metafizzy/flickity

export default $ => {

  const $flickitygallery = $('.carousel.flickitygallery')

  if($flickitygallery.length) { 

    $flickitygallery
    .each(function(){

      const $this = $(this)

      const $view = $this.prev().eq(0)

      const firstimg = $this
      .find('.carousel-cell')
      .on(
        'click',
        function() {

          const url = $(this).data('view')

          $view.css(
            'background-image',
            'url("' + url + '")'
          )
        }
      )
      .first()
      .data('view')

      $view.css(
        'background-image',
        'url("' + firstimg + '")'
      )

      $this.flickity({
        autoPlay: false,
        prevNextButtons: false,
        wrapAround: true,
        pageDots: false,
        lazyLoad: 6
      }) 
    })
  }
}