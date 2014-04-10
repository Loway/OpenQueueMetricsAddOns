import java.io.*;
import javax.servlet.*;
import javax.servlet.http.*;
import java.util.*;
import java.net.URL;
import redstone.xmlrpc.*;

import java.util.ArrayList;

public class xmltest extends HttpServlet {

    public void doPost(HttpServletRequest req, HttpServletResponse res) throws ServletException, IOException {
	doGet(req, res);
    }


    public void doGet(HttpServletRequest req, HttpServletResponse res) throws ServletException, IOException {

        String stUrl = "http://localhost:8080/jupix/xmlrpc.do";
        System.setProperty("org.xml.sax.driver","org.apache.xerces.parsers.SAXParser");

        try {
            XmlRpcClient client = new XmlRpcClient( stUrl, false );
            ArrayList arRes = new ArrayList();
            res.setContentType("text/html");
            PrintWriter out = res.getWriter();
	    
	    String method = req.getParameter("method");
	    String postQuery = req.getParameter("query");
	    String fromDate = req.getParameter("fromdate");
	    String toDate = req.getParameter("todate");
	    String fromTime = req.getParameter("fromTime");
	    String toTime = req.getParameter("toTime");
	    String qNames = req.getParameter("qnames");
	    String agentFilter = req.getParameter("agentfilter");
	    out.println("method: " + method);

	    if (postQuery == null) {
		postQuery = "AgentsDO.ReportAgents|DetailsDO.AgentSessions";
	    }
	    if ((fromDate == null) || (toDate == null)) {
		fromDate = "2009-03-04";
		toDate = "2009-03-04";
	    }
	    if ((fromTime == null) || (toTime == null)) {
		fromTime = "00:00:00";
		toTime = "23:59:59";
	    }
	    if (qNames == null) {
		qNames = "00 All|testqueue";
	    }
	    if (agentFilter == null) {
		agentFilter = "";
	    }
	    if (method == null) {
		method = "QM.stats";
	    }
	    
	    String[] stringList = postQuery.split("\\|");
	    for (int ii=0; ii< stringList.length; ii++) {
		arRes.add(stringList[ii]);
		}
	    Object token = null;

	    if (method.equals("QM.stats")) {
		Object[] parms = { qNames, "robot", "robot", "", "", fromDate + "." + fromTime, toDate + "." + toTime, agentFilter, arRes };
		token = client.invoke( method, parms );
	    } else if (method.equals("QM.realtime")) {
		Object[] parms = { qNames, "robot", "robot", "", agentFilter, arRes };
		token = client.invoke( method, parms );
	    } else if (method.equals("QM.auth")) {
		Object[] parms = { "robot", "robot" };
		token = client.invoke( method, parms );
	    } else {
		out.println("<h2>Invalid method use one of: QM.stats, QM.realtime or QM.auth</h2>");
	    }

            
            out.println("<html><head></head><body>");
	    out.println("<form method=\"post\" action=\"xmltest\">");
	    out.println("<p>Query method:</p>");
	    out.println("<input type=\"text\" name=\"method\" size=\"30\" value=\"" + method + "\">");
	    out.println("<p>Pipe delimited list of response block names to ask for</p>");
	    out.println("<textarea type=\"text\" name=\"query\" rows=6 cols=75\">" + postQuery + "</textarea><br/>");
	    out.println("<p>Date range.  Enter as YYYY-MM-DD!</p>");
	    out.println("From date: <input type=\"text\" name=\"fromdate\" size=\"10\" value=\"" + fromDate + "\">");
	    out.println("From time: <input type=\"text\" name=\"fromTime\" size=\"10\" value=\"" + fromTime + "\"><br/>");
	    out.println("To date: <input type=\"text\" name=\"todate\" size=\"10\" value=\"" + toDate + "\">");
	    out.println("To time: <input type=\"text\" name=\"toTime\" size=\"10\" value=\"" + toTime + "\"><br/>");
	    out.println("<p>Pipe delimited list of queue names</p>");
	    out.println("<input type=\"text\" name=\"qnames\" size=\"50\" value=\"" + qNames + "\">");
	    out.println("<p>Agent name filter: e.g. Agent/1001.  One only!  Or leave blank for all agents</p>");
	    out.println("<input type=\"text\" name=\"agentfilter\" size=\"10\" value=\"" + agentFilter + "\"><br/>");
	    out.println("<input type=\"submit\" value=\"Do Query\">");
	    out.println("</form>");
            HashMap resp = (HashMap) token;
	    ListIterator list = arRes.listIterator();

	    XmlRpcArray result=(XmlRpcArray)resp.get("result");
	    out.println("<p>result " + result + "</p>");

	    while (list.hasNext()) {

		String thisQuery = (String) list.next();
		result=(XmlRpcArray)resp.get(thisQuery);
	    
		out.println("<h1>" + thisQuery + "</h1><br/>");

		out.println("<table border=\"2\">");
		for (int i=0; i<result.size(); i++) {
		    out.println("<tr>");
		    XmlRpcArray resLine = result.getArray(i);
		    for (int i2=0; i2<resLine.size(); i2++) {
			out.println("<td>" + resLine.getString(i2) + "</td>");
		    }
		    out.println("</tr>");
		}
		out.println("</table>");
            
		out.println("<br/>");
	    }

	    out.println("</body></html>");
        } catch ( Exception e ) {
            e.printStackTrace();
        }
    }
}
