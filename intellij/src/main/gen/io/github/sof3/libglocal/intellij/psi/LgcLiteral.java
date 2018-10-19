// This is a generated file. Not intended for manual editing.
package io.github.sof3.libglocal.intellij.psi;

import java.util.List;
import org.jetbrains.annotations.*;
import com.intellij.psi.PsiElement;

public interface LgcLiteral extends PsiElement {

  @NotNull
  List<LgcArgRefComponent> getArgRefComponentList();

  @NotNull
  List<LgcMessageRefComponent> getMessageRefComponentList();

  @NotNull
  List<LgcSpanComponent> getSpanComponentList();

}
