import ClipboardJS from 'clipboard';
import tippy from 'tippy.js';

window.addEventListener( 'load', function () {
  console.log('loaded');
  if ( ClipboardJS.isSupported() ) {
    new ClipboardJS('.clippy');
  
    tippy('.clippy', {
      content: 'Copied to clipboard',
      trigger: 'click',
      theme: 'light',
    });
  }
});
