import { SmartLink } from "@/components/ui/SmartLink";

import { CommunityImage } from "./CommunityImage";

export function MemberCard({ member }: { member: App.Data.Community.MemberSummaryData }) {
    const href = member.url ?? member.uri ?? `/leden/${member.slug}`;

    return (
        <SmartLink className="dlf-member-card" href={href}>
            <span className="dlf-member-card__logo">
                {member.logo ? <CommunityImage asset={member.logo} title={member.title} /> : null}
            </span>
            <span className="dlf-member-card__body">
                <span className="dlf-member-card__name">{member.title}</span>
                <span className="dlf-member-card__location">
                    <span>
                        {member.city}
                        {member.city && member.province ? ", " : null}
                    </span>
                    <span className="dlf-member-card__province">{member.province}</span>
                </span>
            </span>
        </SmartLink>
    );
}

export function InternshipCard({
    internship,
}: {
    internship: App.Data.Community.InternshipCardData;
}) {
    const href = internship.url ?? internship.uri ?? `/stagebank/${internship.slug}`;
    const { member } = internship;

    return (
        <SmartLink className="dlf-member-card" href={href}>
            <span className="dlf-member-card__logo">
                {member.logo ? <CommunityImage asset={member.logo} title={member.title} /> : null}
            </span>
            <span className="dlf-member-card__body">
                <span className="dlf-member-card__name">{internship.title}</span>
                <span className="dlf-member-card__location">
                    <span>
                        {member.city}
                        {member.city && member.province ? ", " : null}
                    </span>
                    <span className="dlf-member-card__province">{member.province}</span>
                </span>
            </span>
        </SmartLink>
    );
}
