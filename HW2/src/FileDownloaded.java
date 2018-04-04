public class FileDownloaded{
    private String url;
    private long size;
    private int outLinkCount;
    private String contentType;

    public FileDownloaded(String url, long size, int outLinkCount, String contentType) {
        this.url = url;
        this.size = size;
        this.outLinkCount = outLinkCount;
        this.contentType = contentType;
    }

    public String getUrl() {
        return url;
    }

    public void setUrl(String url) {
        this.url = url;
    }

    public long getSize() {
        return size;
    }

    public void setSize(long size) {
        this.size = size;
    }

    public int getOutLinkCount() {
        return outLinkCount;
    }

    public void setOutLinkCount(int outLinkCount) {
        this.outLinkCount = outLinkCount;
    }

    public String getContentType() {
        return contentType;
    }

    public void setContentType(String contentType) {
        this.contentType = contentType;
    }
}