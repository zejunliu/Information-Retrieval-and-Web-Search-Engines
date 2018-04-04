import edu.uci.ics.crawler4j.crawler.Page;
import edu.uci.ics.crawler4j.crawler.WebCrawler;
import edu.uci.ics.crawler4j.parser.HtmlParseData;
import edu.uci.ics.crawler4j.url.WebURL;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import java.util.*;
import java.util.regex.Pattern;

public class MyCrawler extends WebCrawler {
    private static final Logger logger = LoggerFactory.getLogger(WebCrawler.class);

    private final static Pattern URLFilter = Pattern.compile(
            ".*(\\.(css|js|json|mid|mp2|mp3|mp4|wav|avi|mov|mpeg|ram|m4v|rm|smil|wmv|swf|wma|zip|rar|gz)(\\?.*)?)$");


    CrawlStat crawlStat;

    public MyCrawler() {
        crawlStat = new CrawlStat();
    }

    /**
     * This method receives two parameters. The first parameter is the page
     * in which we have discovered this new url and the second parameter is
     * the new url. You should implement this function to specify whether
     * the given url should be crawled or not (based on your crawling logic).
     * In this example, we are instructing the crawler to ignore urls that
     * have css, js, git, ... extensions and to only accept urls that start
     * with "http://www.ics.uci.edu/". In this case, we didn't need the
     * referringPage parameter to make the decision.
     */
    @Override
    public boolean shouldVisit(Page referringPage, WebURL webURL) {
        String href = webURL.getURL().toLowerCase();
        return !URLFilter.matcher(href).matches() &&
                (href.startsWith("https://www.foxnews.com/")
                        || href.startsWith("http://www.foxnews.com/"));
    }

    /**
     * This function is called when a page is fetched and ready
     * to be processed by your program.
     */
    @Override
    public void visit(Page page) {

        String url = page.getWebURL().getURL();
        long pageSize = page.getContentData().length;
        int outLinkCount = 0;
        if (page.getParseData() instanceof HtmlParseData) {
            HtmlParseData htmlParseData = (HtmlParseData) page.getParseData();
            Set<WebURL> links = htmlParseData.getOutgoingUrls();
            outLinkCount = links.size();
            for (WebURL webURL : links) {
                String href = webURL.getURL().toLowerCase();
                boolean isInside = false;
                if (href.startsWith("https://www.foxnews.com/") ||
                        href.startsWith("http://www.foxnews.com/"))
                    isInside = true;
                crawlStat.getUrlExtractedList().add(new URLExtracted(webURL.getURL(), isInside));
            }
        }
        String contentType = page.getContentType();
        if (contentType != null && contentType.indexOf(';') != -1)
            contentType = contentType.substring(0, contentType.indexOf(';'));
        crawlStat.getFileDownloadedList().add(new FileDownloaded(url, pageSize, outLinkCount, contentType));

    }

    @Override
    protected void handlePageStatusCode(WebURL webUrl, int statusCode, String statusDescription) {
        crawlStat.getFetchAttemptedList().add(new FetchAttempt(webUrl.getURL(), String.valueOf(statusCode)));
    }

    @Override
    public Object getMyLocalData() {
        return crawlStat;
    }

}