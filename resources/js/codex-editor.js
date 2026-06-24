import EditorJS from '@editorjs/editorjs';
import Header from '@editorjs/header';
import SimpleImage from '@editorjs/simple-image';
import Link from '@editorjs/link';

const editor = new EditorJS({
  /**
   * Id of Element that should contain Editor instance
   */
  holderId: 'editorjs',
  tools: {
    header:{
      class: Header,
      inlineToolbar: ['link'],
      config: {
        placeholder: 'Enter a header',
        levels: [1, 2, 3, 4],
        defaultLevel: 2
      }
    },
    link: Link,
    image: SimpleImage,
  },
  data: {
    // your saved data
  }
});