import java.util.*;

public class CrawlStat {

    private List<FetchAttempt> fetchAttemptedList;
    private List<FileDownloaded> fileDownloadedList;
    private List<URLExtracted> urlExtractedList;


    public CrawlStat() {
        fetchAttemptedList = new ArrayList<>();
        fileDownloadedList = new ArrayList<>();
        urlExtractedList = new ArrayList<>();
    }

    public List<FetchAttempt> getFetchAttemptedList() {
        return fetchAttemptedList;
    }

    public List<FileDownloaded> getFileDownloadedList() {
        return fileDownloadedList;
    }

    public List<URLExtracted> getUrlExtractedList() {
        return urlExtractedList;
    }
}
