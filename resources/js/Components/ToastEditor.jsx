import Editor from '@toast-ui/editor';
import '@toast-ui/editor/dist/toastui-editor.css';
import { useEffect, useRef } from 'react';

/** Editor Markdown TOAST UI untuk form artikel. */
export default function ToastEditor({ initialValue = '', onChange, height = '460px' }) {
    const elRef = useRef(null);
    const changeRef = useRef(onChange);
    changeRef.current = onChange;

    useEffect(() => {
        const editor = new Editor({
            el: elRef.current,
            height,
            initialEditType: 'markdown',
            previewStyle: 'vertical',
            initialValue,
            usageStatistics: false,
            placeholder: 'Tulis isi artikel dalam format Markdown...',
        });

        editor.on('change', () => changeRef.current?.(editor.getMarkdown()));

        return () => editor.destroy();
        // initialValue hanya dipakai saat editor dibuat (modal dibuka ulang untuk data baru).
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    return <div ref={elRef} className="min-h-[360px]" />;
}
