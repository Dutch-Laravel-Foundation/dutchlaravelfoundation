export type AcquisitionPageData = App.Data.Forms.AcquisitionPageData;
export type FormDefinitionData = App.Data.Forms.FormDefinitionData;
export type FormSubmissionStateData = App.Data.Forms.FormSubmissionStateData;

export type AcquisitionPageProps = {
    acquisition: AcquisitionPageData;
    app: {
        captchaSiteKey?: string | null;
        csrfToken: string;
    };
    site: App.Data.SiteShell.SiteShellData;
};
