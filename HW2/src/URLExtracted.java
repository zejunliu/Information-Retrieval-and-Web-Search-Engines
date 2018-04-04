public class URLExtracted{
    private String url;
    private boolean isInside;

    public URLExtracted(String url, boolean isInside) {
        this.url = url;
        this.isInside = isInside;
    }

    public String getUrl() {
        return url;
    }

    public void setUrl(String url) {
        this.url = url;
    }

    public boolean isInside() {
        return isInside;
    }

    public void setInside(boolean inside) {
        isInside = inside;
    }
}