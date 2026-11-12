const base = {
    xmlns: 'http://www.w3.org/2000/svg',
    viewBox: '0 0 24 24',
    fill: 'none',
    stroke: 'currentColor',
    strokeWidth: 1.8,
    strokeLinecap: 'round',
    strokeLinejoin: 'round',
    'aria-hidden': true,
};

const make = (paths) => function Icon({ className = 'h-5 w-5' }) {
    return (
        <svg {...base} className={className}>
            {paths}
        </svg>
    );
};

export const EyeIcon = make(<><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z" /><circle cx="12" cy="12" r="3" /></>);
export const PencilIcon = make(<path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5Z" />);
export const TrashIcon = make(<path d="M3 6h18M8 6V4h8v2M6 6l1 14h10l1-14" />);
export const DotsIcon = make(<><circle cx="12" cy="5" r="1.4" /><circle cx="12" cy="12" r="1.4" /><circle cx="12" cy="19" r="1.4" /></>);
export const PlusIcon = make(<path d="M12 5v14M5 12h14" />);
export const DownloadIcon = make(<path d="M12 3v12m0 0-4-4m4 4 4-4M4 21h16" />);
export const UploadIcon = make(<path d="M12 21V9m0 0-4 4m4-4 4 4M4 3h16" />);
export const SparklesIcon = make(<path d="M12 3v4M12 17v4M3 12h4M17 12h4M6.3 6.3l2.5 2.5M15.2 15.2l2.5 2.5M6.3 17.7l2.5-2.5M15.2 8.8l2.5-2.5" />);
export const XIcon = make(<path d="M6 6l12 12M18 6 6 18" />);
export const CheckIcon = make(<path d="m5 12 5 5 9-10" />);
export const ArrowLeftIcon = make(<path d="M19 12H5m0 0 6-6m-6 6 6 6" />);

export function Spinner({ className = 'h-4 w-4' }) {
    return (
        <svg className={`${className} animate-spin`} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle cx="12" cy="12" r="10" stroke="currentColor" strokeOpacity="0.25" strokeWidth="4" />
            <path d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" strokeWidth="4" strokeLinecap="round" />
        </svg>
    );
}
