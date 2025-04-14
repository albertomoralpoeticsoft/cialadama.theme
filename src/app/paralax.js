export default $ => {

  const randomindex = Math.floor(Math.random() * window.ladama.backgroundimages.length)
  const randomimage = window.ladama.backgroundimages[randomindex].url

  $('body').wrapInner('<div id="Paralax"></div')
  $('#Paralax').prepend(`
    <div 
      class="Background"
      style="background-image: url(${ randomimage })"
    >
    </div>
  `)
}