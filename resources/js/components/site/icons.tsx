export function SearchIcon() {
    return (
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" aria-hidden="true">
            <path
                fillRule="evenodd"
                d="M9.965 11.026a5 5 0 1 1 1.06-1.06l2.755 2.754a.75.75 0 1 1-1.06 1.06l-2.755-2.754ZM10.5 7a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0Z"
                clipRule="evenodd"
            />
        </svg>
    );
}

export function ChevronIcon({ className }: { className: string }) {
    return (
        <svg
            className={className}
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 16 16"
            aria-hidden="true"
        >
            <path
                fillRule="evenodd"
                d="M4.22 6.22a.75.75 0 0 1 1.06 0L8 8.94l2.72-2.72a.75.75 0 1 1 1.06 1.06l-3.25 3.25a.75.75 0 0 1-1.06 0L4.22 7.28a.75.75 0 0 1 0-1.06Z"
                clipRule="evenodd"
            />
        </svg>
    );
}

export function MenuIcon() {
    return (
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M3 6h18M3 12h18M3 18h18" />
        </svg>
    );
}

export function CloseIcon() {
    return (
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="m5 5 14 14M19 5 5 19" />
        </svg>
    );
}

export function CheckIcon() {
    return (
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" aria-hidden="true">
            <path
                fillRule="evenodd"
                d="M12.416 3.376a.75.75 0 0 1 .208 1.04l-5 7.5a.75.75 0 0 1-1.154.114l-3-3a.75.75 0 0 1 1.06-1.06l2.353 2.353 4.493-6.74a.75.75 0 0 1 1.04-.207Z"
                clipRule="evenodd"
            />
        </svg>
    );
}
