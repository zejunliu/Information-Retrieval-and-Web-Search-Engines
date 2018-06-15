import networkx as nx

graph = nx.read_edgelist("/Users/zejunliu/Desktop/HW4/edgesList.txt", create_using=nx.DiGraph())

pr = nx.pagerank(graph, alpha=0.85, personalization=None, max_iter=100, tol=1.0e-6, nstart=None, weight='weight', dangling=None)
outputFile = "/Users/zejunliu/Desktop/HW4/external_pageRankFile.txt"
f = open(outputFile, "w")
idPrefix = "/Users/zejunliu/Desktop/solr-7.3.0/crawl_data/"
for id in pr:
	f.write(idPrefix + id + "=" + str(pr[id]) + "\n")
f.close()