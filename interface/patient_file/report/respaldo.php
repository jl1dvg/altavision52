if($res[1] == 'treatment_plan') {
                           ?>
                           <table CELLSPACING=0 COLS=1 RULES=NONE BORDER=0 WIDTH=100%>
                               <TR>
                                   <TD class="lineatitulo"><b>5 PLAN DE TRATAMIENTO RECOMENDADO</B></TD>
                               </TR>
                               <TR>
                                   <TD class="linearesumen">
                                       <?php
                                       $queryPlan = "select tp.admit_date AS alta, tp.recommendation_for_follow_up AS recomendacion, tp.treatment_received AS tratamiento
                                          from forms AS f
                                          LEFT JOIN form_treatment_plan AS tp ON (tp.id = f.form_id)
                                          where
                                          f.pid=? and
                                          f.encounter=? and
                                          f.form_id
                                          f.formdir='treatment_plan' and
                                          f.deleted = 0 ";
                                       $plan = sqlQuery($queryPlan, array($pid, $form_encounter, $form_id));
                                       echo wordwrap($plan['recomendacion'], 165, "</td></tr><tr><td class='linearesumen'>");
                                       ?>
                                   </TD>
                               </TR>
                               <TR>
                                   <TD class="linearesumen"><BR></TD>
                               </TR>
                               <TR>
                                   <TD class="ultimalinea" colspan="13" ALIGN=LEFT><BR></TD>
                               </TR>
                           </table>
                           <?php
                       }
                           ?>

        <table CELLSPACING=0 RULES=NONE BORDER=0 WIDTH=100%>
                               <TR>
                                   <TD colspan="8" ALIGN=LEFT><BR></TD>
                                   <TD rowspan="3" ALIGN=CENTER valign="top" BGCOLOR="#FFFFFF"><FONT SIZE=1>
                                       </FONT></TD>
                               </TR>
                               <TR>
                                   <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 5px solid #808080; border-right: 1px solid #808080" width="7%" HEIGHT=20 ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>SALA</FONT></TD>
                                   <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" width="7%" ALIGN=CENTER VALIGN=MIDDLE SDNUM="1033;1033;D-MMM-YY"><B><FONT SIZE=1><BR></FONT></B></TD>
                                   <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" width="7%" ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>CAMA</FONT></TD>
                                   <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" width="7%" ALIGN=CENTER VALIGN=MIDDLE SDNUM="1033;0;[H]:MM:SS"><B><FONT SIZE=1><BR></FONT></B></TD>
                                   <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" width="7%" ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>PROFESIONAL</FONT></TD>
                                   <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#FFFFFF"><FONT SIZE=1>
                                           <?php
                                           echo getProviderName($providerID);
                                           ?>
                                       </FONT></TD>
                                   <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 1px solid #808080" width="7%" ALIGN=CENTER VALIGN=MIDDLE><FONT SIZE=1>
                                           <?php
                                           echo getProviderRegistro($providerID);
                                           ?>
                                       </FONT></TD>
                                   <TD STYLE="border-top: 5px solid #808080; border-bottom: 5px solid #808080; border-left: 1px solid #808080; border-right: 5px solid #808080" width="7%" ALIGN=CENTER VALIGN=MIDDLE BGCOLOR="#CCFFCC"><FONT SIZE=1>FIRMA</FONT></TD>

                               </TR>
                               <TR>
                                   <TD colspan="8" ALIGN=LEFT><BR></TD>
                               </TR>
                               <TR>
                                   <TD colspan="6" HEIGHT=24 ALIGN=LEFT VALIGN=TOP><B><FONT SIZE=1 COLOR="#000000">SNS-MSP / HCU-form.053 / 2008</FONT></B></TD>
                                   <TD colspan="3" ALIGN=RIGHT VALIGN=TOP><B><FONT SIZE=3 COLOR="#000000">CONTRAREFERENCIA</FONT></B></TD>
                               </TR>
                               </TBODY>
                           </TABLE>
