import java.io.BufferedReader;
import java.io.File;
import java.io.FileReader;
import java.io.IOException;
import java.io.InputStreamReader;
import java.net.ServerSocket;
import java.net.Socket;
import java.nio.file.Files;
import java.nio.file.Paths;
import java.util.concurrent.ExecutionException;
import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;
import java.util.concurrent.Future;
import java.util.concurrent.TimeUnit;
import java.util.concurrent.TimeoutException;

class Config {

	/**
	 * @param args
	 */
	public static final String webRoot = "C:\\wamp\\www\\";
	public static final String assignmentDir = "C:\\wamp\\assignments\\";
	public static final String uploadDir = "C:\\wamp\\www\\upload\\";
	public static final String securityPolicy = "C:\\wamp\\www\\studentpolicy.policy";
	public static final String inputDir = "C:\\wamp\\www\\input\\";

	public static void main(String[] args) {
		// TODO Auto-generated method stub

	}

}

class ClassExecutor implements Runnable {
	private static final int COMPILATION_ERROR = 1;
	private static final int RUNTIME_ERROR = 2;
	private static final int SECURITY_ERROR = 3;
	private static final int ACCEPTED = 4;
	private static final int WRONG_ANSWER = 5;
	private String result = "";
	private String filename = "";
	private String diagnostic = "";
	Socket s;

	public ClassExecutor(String fn) {
		filename = fn;
	}

	public String getDiagnostic() {
		return diagnostic;
	}

	public void run() {
		try {

			BufferedReader sbr = new BufferedReader(new InputStreamReader(
					s.getInputStream()));
			String problemName = sbr.readLine() + ".txt";
			String file = sbr.readLine();
			String user = sbr.readLine();
			System.out.println("User = " + user);
			System.out
					.println("JAVAC " + Config.uploadDir + user + "\\" + file);
			Process compile = Runtime.getRuntime().exec(
					"javac " + Config.uploadDir + user + "\\" + file);
			File input = new File(Config.inputDir + problemName);

			BufferedReader compileR = new BufferedReader(new InputStreamReader(
					compile.getErrorStream()));
			String comp_err = compileR.readLine();
			if (comp_err != null) {
				result = COMPILATION_ERROR + "";
				System.out.println(comp_err);
				return;
			}
		
			System.out.println("Feeding: " + "java -Duser.dir="
					+ Config.uploadDir
					+ user
					+ " -Djava.security.manager -Djava.security.policy="
					+ Config.securityPolicy + " "
					+ file.substring(0, file.length() - 5) + " < "
					+ Config.inputDir + problemName);
			String arg[] = {
					
				"java",
				"-Duser.dir="+Config.uploadDir + user,
				
				"-Djava.security.manager -Djava.security.policy="
				+ Config.securityPolicy,
				 file.substring(0, file.length() - 5)
				
				
			};
			
			ProcessBuilder pb = new ProcessBuilder(arg);
			if(input.exists())
				pb.redirectInput(input);
			Process proc = pb.start();
					/*
					Runtime
					.getRuntime()
					.exec("java -Duser.dir="
							+ Config.uploadDir
							+ user
							+ " -Djava.security.manager -Djava.security.policy="
							+ Config.securityPolicy + " "
							+ file.substring(0, file.length() - 5) + " < "
							+ Config.inputDir + problemName);
			
			  if(input.exists()) { BufferedReader inputReader = new
			  BufferedReader(new FileReader(input)); String inLine = "";
			  while((inLine = inputReader.readLine()) != null) {
			  proc.getOutputStream().write(inLine.getBytes()); } }
			 */
			 proc.waitFor();
			BufferedReader stdError = new BufferedReader(new InputStreamReader(
					proc.getErrorStream()));

			// read the output from the command
			String s = null;

			// read any errors from the attempted command
			if(stdError.ready())
				while ((s = stdError.readLine()) != null) {

					if (s.contains("Exception in thread \"main\" java.security.AccessControlException: access denied ")) {
						result = "" + SECURITY_ERROR;
						diagnostic += (s + System.lineSeparator());
						// System.out.println("SEC");
						// System.exit(SECURITY_ERROR);
						// return;
					} else if (s.contains("Exception")) {
						// result = "Runtime Error";
						result = "" + RUNTIME_ERROR;
						diagnostic += s;
						diagnostic += System.lineSeparator();
						System.out.println("Diag = " + diagnostic);
						// System.out.println("RTE");

						// return;

					}

				}
			
			if (diagnostic.length() > 0) {
				return;
			}
			byte expec[] = Files.readAllBytes(Paths.get(Config.assignmentDir
					+ problemName));

			String expectedOutput = new String(expec).replace("\r", "");

			String codeS = "";
			 BufferedReader codeOutput = new BufferedReader(new
			 InputStreamReader(proc.getInputStream()));
			byte code_buffer[] = new byte[2048];
			int read_bytes = 0;
			if(codeOutput.ready())
			while ((read_bytes = proc.getInputStream().read(code_buffer)) > 0) {
				codeS += new String(code_buffer, 0, read_bytes);
				codeS += '\n';
			}
			if (codeS.indexOf('\n') != -1)
				codeS = codeS.substring(0, codeS.lastIndexOf('\n'));
			if (codeS.indexOf(System.lineSeparator()) != -1)
				codeS = codeS.substring(0, codeS.lastIndexOf(System.lineSeparator()));
			codeS = codeS.replace("\r", "");
			 System.out.println("Expected: " + expectedOutput);
			 System.out.println("Code: " + codeS);
			if (codeS.equals(expectedOutput)) {
				result = "" + ACCEPTED;
			} else
				result = "" + WRONG_ANSWER;
			System.out.println(codeS.length() + " vs " + expectedOutput.length());
			// System.out.println(result);

		} catch (Exception e) {
			e.printStackTrace();
		}
	}

	public String getResult() {
		return result;
	}

}

class SocketHandler implements Runnable {

	static ExecutorService es;
	private String result;
	private String filename;
	private String diagnostic;
	Future<?> fut;
	Socket s;

	static {
		es = Executors.newCachedThreadPool();
	}

	public SocketHandler(String fn) {
		filename = fn;
	}

	public String getDiagnostic() {
		return diagnostic;
	}

	public void run() {

		try {
			ClassExecutor ce = new ClassExecutor(filename);
			ce.s = s;
			fut = es.submit(ce);
			fut.get(150, TimeUnit.SECONDS);

			result = ce.getResult();
			diagnostic = ce.getDiagnostic();

			s.getOutputStream().write(
					(result + System.lineSeparator()).getBytes());
			if (diagnostic.length() > 0) {
				s.getOutputStream().write(diagnostic.getBytes());
			}

		} catch (TimeoutException te) {
			fut.cancel(true);
			try {
				s.getOutputStream().write(
						("6" + System.lineSeparator()).getBytes());
			} catch (Exception e) {
			}
		} catch (Exception e) {
		} finally {
			fut.cancel(true);
		}

	}

	public String getResult() {
		return result;
	}

}

public class Server {

	public static void main(String[] args) throws IOException,
			InterruptedException, ExecutionException, TimeoutException {

		ServerSocket ss = new ServerSocket(5555);
		ExecutorService executor = Executors.newCachedThreadPool();
		while (true) {
			Socket s = null;
			SocketHandler sh = null;
			s = ss.accept();
			// byte b[] = new byte[100];
			// s.getInputStream().read(b,0,100);
			String fname = "";

			sh = new SocketHandler(fname);
			sh.s = s;

			executor.submit(sh);

		}

	}
}
