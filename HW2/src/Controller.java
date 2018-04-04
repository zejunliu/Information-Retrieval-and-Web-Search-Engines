import edu.uci.ics.crawler4j.crawler.CrawlConfig;
import edu.uci.ics.crawler4j.crawler.CrawlController;
import edu.uci.ics.crawler4j.fetcher.PageFetcher;
import edu.uci.ics.crawler4j.robotstxt.RobotstxtConfig;
import edu.uci.ics.crawler4j.robotstxt.RobotstxtServer;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import java.io.BufferedWriter;
import java.nio.file.Files;
import java.nio.file.Paths;
import java.util.*;

public class Controller {

    private static final Logger logger = LoggerFactory.getLogger(Controller.class);

    private final static String[] ContentFilterArr = {"text/html",
            "application/pdf", "application/msword",
            "image/jpeg", "image/png", "image/gif", "image/x-icon", "image/svg+xml", "image/tiff", "image/webp"};
    private final static Set<String> ContentFilter = new HashSet<>(Arrays.asList(ContentFilterArr));

    public static void main(String[] args) throws Exception {
        String crawlStorageFolder = "data/crawl";
        int numberOfCrawlers = 32;

        CrawlConfig crawlConfig = new CrawlConfig();
        crawlConfig.setMaxDownloadSize(Integer.MAX_VALUE);
        crawlConfig.setCrawlStorageFolder(crawlStorageFolder);
        crawlConfig.setMaxPagesToFetch(20000);
        crawlConfig.setMaxDepthOfCrawling(16);
        crawlConfig.setIncludeBinaryContentInCrawling(true);

        /*
         * Instantiate the controller for this crawl.
         */
        PageFetcher pageFetcher = new PageFetcher(crawlConfig);
        RobotstxtConfig robotstxtConfig = new RobotstxtConfig();
        RobotstxtServer robotstxtServer = new RobotstxtServer(robotstxtConfig, pageFetcher);
        CrawlController crawlController = new CrawlController(crawlConfig, pageFetcher, robotstxtServer);
        /*
         * For each crawl, you need to add some seed urls. These are the first
         * URLs that are fetched and then the crawler starts following links
         * which are found in these pages
         */
        crawlController.addSeed("https://www.foxnews.com/");
        /*
         * Start the crawl. This is a blocking operation, meaning that your code
         * will reach the line after this only when crawling is finished.
         */
        crawlController.start(MyCrawler.class, numberOfCrawlers);

        BufferedWriter fetch_file=Files.newBufferedWriter(Paths.get("fetch_FOX_News.csv"));
        BufferedWriter visit_file=Files.newBufferedWriter(Paths.get("visit_Fox_News.csv"));
        BufferedWriter urls_file=Files.newBufferedWriter(Paths.get("urls_Fox_News.csv"));

        int fetchesTotal = 0;
        int fetchesSucceeded = 0;
        int fetchesAborted = 0;
        long extractedTotal = 0;
        Set<String> extractedUnique=new HashSet<>();
        Set<String> extractedInside = new HashSet<>();
        Set<String> extractedOutside = new HashSet<>();
        Map<String, Integer> statusCodeMap = new HashMap<>();
        Map<String, Integer> fileSizeMap = new HashMap<>();
        Map<String, Integer> contentTypeMap = new HashMap<>();

        List<Object> crawlersLocalData = crawlController.getCrawlersLocalData();

        for (Object localData : crawlersLocalData) {
            CrawlStat stat = (CrawlStat) localData;
            List<FetchAttempt> fetchAttemptList=stat.getFetchAttemptedList();
            fetchesTotal+=fetchAttemptList.size();

            for(FetchAttempt fetchAttempt:fetchAttemptList){
                String statusCode=fetchAttempt.getStatusCode();
                if(statusCode.startsWith("2")){
                    fetchesSucceeded++;
                }
                else {
                    fetchesAborted++;
                }
                statusCodeMap.put(statusCode,statusCodeMap.getOrDefault(statusCode,0)+1);
                fetch_file.write(fetchAttempt.getUrl()+","+fetchAttempt.getStatusCode()+"\n");
            }

            List<FileDownloaded> fileDownloadedList=stat.getFileDownloadedList();
            for(FileDownloaded fileDownloaded:fileDownloadedList){
                long size=fileDownloaded.getSize();
                String sizeLevel=pageSizeToLevel(size);
                fileSizeMap.put(sizeLevel,fileSizeMap.getOrDefault(sizeLevel,0)+1);

                String contentType=fileDownloaded.getContentType();
                if(ContentFilter.contains(contentType)){
                    contentTypeMap.put(contentType,contentTypeMap.getOrDefault(contentType,0)+1);
                }
                visit_file.write(fileDownloaded.getUrl()+","+fileDownloaded.getSize()+","
                        +fileDownloaded.getOutLinkCount()+","+fileDownloaded.getContentType()+"\n");
            }

            List<URLExtracted> urlExtractedList=stat.getUrlExtractedList();
            for(URLExtracted urlExtracted:urlExtractedList){
                String url=urlExtracted.getUrl();
                extractedTotal++;
                extractedUnique.add(url);
                if(urlExtracted.isInside()){
                    extractedInside.add(url);
                }
                else{
                    extractedOutside.add(url);
                }
                urls_file.write(urlExtracted.getUrl()+","+(urlExtracted.isInside()?"OK":"N_OK"));
            }
        }
        fetch_file.close();visit_file.close();urls_file.close();

        BufferedWriter stat_file=Files.newBufferedWriter(Paths.get("CrawlReport_FOX_News.txt"));
        stat_file.write("Name: Zejun Liu\n" +
                "USC ID: 6992071555\n" +
                "News site crawled: foxnews.com\n\n");
        stat_file.write("Fetch Statistics\n" +
                "================\n" +
                "# fetches attempted:"+fetchesTotal+"\n"+
                "# fetches succeeded:"+fetchesSucceeded+"\n" +
                "# fetches failed or aborted:"+fetchesAborted+"\n\n");
        stat_file.write("Outgoing URLs:\n" +
                "==============\n" +
                "Total URLs extracted:"+extractedTotal+"\n"+
                "# unique URLs extracted:"+extractedUnique.size()+"\n" +
                "# unique URLs within News Site:"+extractedInside.size()+"\n" +
                "# unique URLs outside News Site:"+extractedOutside.size()+"\n\n");
        stat_file.write("Status Codes:\n" +
                "=============\n");
        for(Map.Entry<String,Integer> entry:statusCodeMap.entrySet()){
            String statusCode=entry.getKey();
            String name=HttpStatus.getByCode(statusCode).getName();
            int count=entry.getValue();
            stat_file.write(statusCode+" "+name+":"+count+"\n");
        }
        stat_file.write("\n");
        stat_file.write("File Sizes:\n" +
                "===========\n");
        for(Map.Entry<String,Integer> entry:fileSizeMap.entrySet()){
            stat_file.write(entry.getKey()+":"+entry.getValue()+"\n");
        }
        stat_file.write("\n");
        stat_file.write("Content Types:\n" +
                "==============\n");
        for(Map.Entry<String,Integer> entry:contentTypeMap.entrySet()){
            stat_file.write(entry.getKey()+":"+entry.getValue()+"\n");
        }
        stat_file.close();
    }

    private static String pageSizeToLevel(long pageSize) {
        if (pageSize < 1024)
            return "< 1KB";
        if (pageSize < 10 * 1024)
            return "1KB ~ <10KB";
        if (pageSize < 100 * 1024)
            return "10KB ~ <100KB";
        if (pageSize < 1024 * 1024)
            return "100KB ~ <1MB";
        return ">= 1MB";
    }

}