import { Popover } from "@base-ui/react/popover";
import type { ReactNode } from "react";

type InfoPopoverProps = {
    children: ReactNode;
    content: ReactNode;
};

// Opens on hover and on tap, so touch devices can read the explanation too.
export function InfoPopover({ children, content }: InfoPopoverProps) {
    return (
        <Popover.Root>
            <Popover.Trigger openOnHover delay={150} className="dlf-info-popover__trigger">
                {children}
            </Popover.Trigger>
            <Popover.Portal>
                <Popover.Positioner side="top" sideOffset={8} className="dlf-info-popover">
                    <Popover.Popup className="dlf-info-popover__popup">{content}</Popover.Popup>
                </Popover.Positioner>
            </Popover.Portal>
        </Popover.Root>
    );
}
