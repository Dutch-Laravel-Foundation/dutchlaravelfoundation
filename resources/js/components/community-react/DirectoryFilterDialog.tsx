import { Button } from "@base-ui/react/button";
import { Dialog } from "@base-ui/react/dialog";

type FilterGroup = {
    active: readonly string[];
    label: string;
    options: readonly string[];
    onToggle: (value: string) => void;
};

type DirectoryFilterDialogProps = {
    activeCount: number;
    applyLabel: string;
    applyWidth?: number;
    groups: readonly FilterGroup[];
    id: string;
    onClear: () => void;
    title: string;
    triggerClassName?: string;
    triggerResultLabel?: string;
};

function FilterIcon() {
    return (
        <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 16 16"
            fill="currentColor"
            aria-hidden="true"
        >
            <path d="M2 4.75A.75.75 0 0 1 2.75 4h10.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 4.75ZM4 8a.75.75 0 0 1 .75-.75h6.5a.75.75 0 0 1 0 1.5h-6.5A.75.75 0 0 1 4 8Zm2.25 3.25a.75.75 0 0 0 0 1.5h3.5a.75.75 0 0 0 0-1.5h-3.5Z" />
        </svg>
    );
}

export function DirectoryFilterDialog({
    activeCount,
    applyLabel,
    applyWidth = 196,
    groups,
    id,
    onClear,
    title,
    triggerClassName,
    triggerResultLabel,
}: DirectoryFilterDialogProps) {
    return (
        <Dialog.Root>
            <Dialog.Trigger
                className={`dlf-members-filter-button${triggerClassName ? ` ${triggerClassName}` : ""}`}
            >
                <FilterIcon />
                <span>Filters</span>
                {activeCount ? (
                    <span className="dlf-members-filter-count">{activeCount}</span>
                ) : null}
                {triggerResultLabel ? (
                    <span className="dlf-members-result-count--mobile">{triggerResultLabel}</span>
                ) : null}
            </Dialog.Trigger>

            <Dialog.Portal>
                <Dialog.Viewport className="dlf-members-modal">
                    <Dialog.Popup
                        id={id}
                        className="dlf-members-dialog"
                        aria-labelledby={`${id}-title`}
                    >
                        <div className="dlf-members-dialog__head">
                            <Dialog.Title id={`${id}-title`}>{title}</Dialog.Title>
                            <Dialog.Close
                                type="button"
                                className="dlf-members-dialog__close"
                                aria-label="Filters sluiten"
                            >
                                &times;
                            </Dialog.Close>
                        </div>

                        <div className="dlf-members-dialog__body">
                            {groups.map((group) => (
                                <div className="dlf-members-filter-group" key={group.label}>
                                    <span className="dlf-community-kicker">{group.label}</span>
                                    <div className="dlf-members-filter-options">
                                        {group.options.map((option) => {
                                            const active = group.active.includes(option);

                                            return (
                                                <Button
                                                    type="button"
                                                    className={`dlf-members-chip${active ? " is-active" : ""}`}
                                                    aria-pressed={active}
                                                    onClick={() => group.onToggle(option)}
                                                    key={option}
                                                >
                                                    {option}
                                                </Button>
                                            );
                                        })}
                                    </div>
                                </div>
                            ))}
                        </div>

                        <div className="dlf-members-dialog__foot">
                            <Button type="button" className="dlf-members-clear" onClick={onClear}>
                                Filters wissen
                            </Button>
                            <Dialog.Close
                                type="button"
                                className="dlf-community-button"
                                style={{ width: applyWidth }}
                            >
                                <span className="dlf-community-button__face">{applyLabel}</span>
                                <span className="dlf-community-button__back" aria-hidden="true" />
                            </Dialog.Close>
                        </div>
                    </Dialog.Popup>
                </Dialog.Viewport>
            </Dialog.Portal>
        </Dialog.Root>
    );
}
