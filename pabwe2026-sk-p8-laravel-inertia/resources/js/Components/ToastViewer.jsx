import Viewer from '@toast-ui/editor/dist/toastui-editor-viewer';
import '@toast-ui/editor/dist/toastui-editor-viewer.css';
import { useEffect, useRef } from 'react';

/** Penampil Markdown TOAST UI untuk membaca artikel. */
export default function ToastViewer({ value = '' }) {
    const elRef = useRef(null);

    useEffect(() => {
        const viewer = new Viewer({
            el: elRef.current,
            initialValue: value,
            usageStatistics: false,
        });

        return () => viewer.destroy();
    }, [value]);

    return <div ref={elRef} className="toast-viewer-wrap" />;
}
