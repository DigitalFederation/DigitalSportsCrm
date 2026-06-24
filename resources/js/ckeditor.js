import  CKEditor  from '../vendor/ckeditor5/build/ckeditor';
console.log('*** Loaded CKEditor ***');
CKEditor
    .create( document.querySelector( '#editor' ) )
    .then( editor => {
        console.log( editor );
    } )
    .catch( error => {
        console.error( error );
    } );