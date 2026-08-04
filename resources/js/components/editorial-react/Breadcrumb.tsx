import { SmartLink } from "@/components/ui/SmartLink";

type BreadcrumbProps = {
    current: string;
    href: string;
    label: string;
};

export function Breadcrumb({ current, href, label }: BreadcrumbProps) {
    return (
        <nav className="editorial-breadcrumb" aria-label="Kruimelpad">
            <SmartLink className="editorial-breadcrumb__link" href={href}>
                {label}
            </SmartLink>
            <span className="editorial-breadcrumb__separator" aria-hidden="true">
                /
            </span>
            <span className="editorial-breadcrumb__current" aria-current="page">
                {current}
            </span>
        </nav>
    );
}
