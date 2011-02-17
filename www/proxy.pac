/***********************************************************
** TJHSST Proxy Auto-Configuration Script                 **
** For use with TJHSST school databases                   **
** Use is restricted to TJHSST students and faculty ONLY. **
** All other use is prohibited.                           **
** Originally contributed by William Yang.                **
** Autogenerated version by Brandon Vargo.                **
************************************************************/

function FindProxyForURL(url, host)
{
	if (
		dnsDomainIs(host, ".abc-clio.com") ||
		dnsDomainIs(host, "www.accessscience.com") ||
		dnsDomainIs(host, ".eb.com") ||
		dnsDomainIs(host, "library.cqpress.com") ||
		dnsDomainIs(host, ".earthscape.org") ||
		dnsDomainIs(host, "search.ebscohost.com") ||
		dnsDomainIs(host, "ehrafworldcultures.yale.edu") ||
		dnsDomainIs(host, "infotrac.galegroup.com") ||
		dnsDomainIs(host, ".grolier.com") ||
		dnsDomainIs(host, "jchemed.chem.wisc.edu") ||
		dnsDomainIs(host, "www.jstor.org") ||
		dnsDomainIs(host, "web.lexis-nexis.com") ||
		dnsDomainIs(host, "www.noodletools.com") ||
		dnsDomainIs(host, "dictionary.oed.com") ||
		dnsDomainIs(host, "poll.orspub.com") ||
		dnsDomainIs(host, "proquestk12.com") ||
		dnsDomainIs(host, "www.sciencedirect.com") ||
		dnsDomainIs(host, "hwwilsonweb.com") ||
		dnsDomainIs(host, ".nature.com") ||
		dnsDomainIs(host, "portal.bigchalk.com") ||
		dnsDomainIs(host, ".umi.com") ||
		dnsDomainIs(host, ".culturegrams.com") ||
		dnsDomainIs(host, ".acs.org") ||
		dnsDomainIs(host, ".opticsinfobase.org") ||
		dnsDomainIs(host, "www.worldbookonline.com") ||
		dnsDomainIs(host, ".tumblebooks.com") ||
		dnsDomainIs(host, "hsus.cambridge.org") ||
		dnsDomainIs(host, ".marshallcavendishdigital.com")

	)
			return "PROXY local.border.tjhsst.edu:8080";
		else
			return "DIRECT";
}
