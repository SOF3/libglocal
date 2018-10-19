// This is a generated file. Not intended for manual editing.
package io.github.sof3.libglocal.intellij.psi;

import java.util.List;
import org.jetbrains.annotations.*;
import com.intellij.psi.PsiElement;

public interface LgcMessageRefComponent extends PsiElement {

  @NotNull
  List<LgcAttribute> getAttributeList();

  @NotNull
  PsiElement getCloseBrace();

  @NotNull
  PsiElement getMessageRefStart();

  @Nullable
  PsiElement getDynamic();

  @NotNull
  PsiElement getMessageId();

}
