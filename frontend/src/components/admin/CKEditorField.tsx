import { CKEditor } from '@ckeditor/ckeditor5-react';
import { Bold, ClassicEditor, Essentials, Heading, Italic, Link, List, Paragraph } from 'ckeditor5';
import 'ckeditor5/ckeditor5.css';

interface CKEditorFieldProps {
  value: string;
  onChange: (html: string) => void;
}

export function CKEditorField({ value, onChange }: CKEditorFieldProps) {
  return (
    <CKEditor
      editor={ClassicEditor}
      data={value}
      config={{
        licenseKey: 'GPL',
        plugins: [Essentials, Paragraph, Heading, Bold, Italic, List, Link],
        toolbar: ['heading', '|', 'bold', 'italic', 'bulletedList', 'numberedList', 'link', '|', 'undo', 'redo'],
      }}
      onChange={(_event, editor) => onChange(editor.getData())}
    />
  );
}
