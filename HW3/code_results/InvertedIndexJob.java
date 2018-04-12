import java.io.IOException;
import java.util.StringTokenizer;
import java.util.*;
import org.apache.hadoop.conf.Configuration;
import org.apache.hadoop.fs.Path;
import org.apache.hadoop.io.IntWritable;
import org.apache.hadoop.io.Text;
import org.apache.hadoop.mapreduce.Job;
import org.apache.hadoop.mapreduce.Mapper;
import org.apache.hadoop.mapreduce.Reducer;
import org.apache.hadoop.mapreduce.lib.input.FileInputFormat;
import org.apache.hadoop.mapreduce.lib.output.FileOutputFormat;
public class InvertedIndexJob {
  public static class TokenizerMapper extends Mapper<Object, Text, Text, Text>{
    private Text word = new Text();
    public void map(Object key, Text value, Context context) throws IOException, InterruptedException {
      private Text docId = new Text();
      docId.set(value.toString().split("\t")[0]);
      StringTokenizer itr = new StringTokenizer(value.toString().split("\t")[1]);
      while (itr.hasMoreTokens()) {
        word.set(itr.nextToken());
        context.write(word, docId);
      }
    }
  }
  public static class IntSumReducer extends Reducer<Text, Text, Text, Text> {
    public void reduce(Text key, Iterable<Text> values, Context context) throws IOException, InterruptedException {
      HashMap<String, Integer> map = new HashMap<String, Integer>();
      for (Text val : values) {
        if(map.containsKey(val.toString())){
          map.put(val.toString(), map.get(val.toString())+1);
        }else{
          map.put(val.toString(), 1);
        }
      }
      StringBuilder output = new StringBuilder();
      Text out = new Text();
      for(Map.Entry<String,Integer> entry:map.entrySet()){
        output.append(entry.getKey()).append(":").append(entry.getValue()).append("\t");
      }
      out.set(output.toString());
      context.write(key, out);
    }
  }
  public static void main(String[] args) throws Exception {
    Configuration conf = new Configuration();
    Job job = Job.getInstance(conf, "Inverted Index");
    job.setJarByClass(InvertedIndexJob.class);
    job.setMapperClass(TokenizerMapper.class);
    job.setReducerClass(IntSumReducer.class);
    job.setOutputKeyClass(Text.class);
    job.setOutputValueClass(Text.class);
    FileInputFormat.addInputPath(job, new Path(args[0]));
    FileOutputFormat.setOutputPath(job, new Path(args[1]));
    System.exit(job.waitForCompletion(true) ? 0 : 1);
  }
}