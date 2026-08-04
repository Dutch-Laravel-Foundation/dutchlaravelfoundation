type FormSuccessProps = {
    className?: string;
    heading: string;
    headingClassName?: string;
    message: string;
};

export function FormSuccess({
    className = "",
    heading,
    headingClassName,
    message,
}: FormSuccessProps) {
    return (
        <div className={`dlf-form-success ${className}`.trim()} role="status">
            <span className="dlf-form-success__icon" aria-hidden="true">
                <img src="/assets/img/development-speed.svg" width="44" height="44" alt="" />
            </span>
            <h2 className={headingClassName}>{heading}</h2>
            <p>{message}</p>
        </div>
    );
}
